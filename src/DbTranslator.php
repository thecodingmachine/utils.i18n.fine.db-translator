<?php
/*
 * Copyright (c) 2012-2015 David NEGRIER
 *
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Validator\MoufValidatorResult;

use Mouf\Validator\MoufValidatorInterface;

use Mouf\MoufManager;

use Mouf\Utils\I18n\Fine\FineMessageLanguage;

use Mouf\Utils\I18n\Fine\LanguageDetectionInterface;
use Mouf\Utils\I18n\Fine\Common\Ui\EditTranslationHelperTrait;
use Mouf\Utils\I18n\Fine\TranslatorInterface;
use Mouf\Utils\I18n\Fine\Common\Ui\EditTranslationInterface;

/**
 * The DbTranslator stores FINE translations in database.
 *
 * @author David NEGRIER
 */
class DbTranslator implements TranslatorInterface, EditTranslationInterface  {
	use EditTranslationHelperTrait;

	/**
	 * Message list
	 *
	 * @var array
	 */
	private $msg = null;

	/**
	 * The path to the directory storing the translations.
	 * <p>The directory path should end with a "/".</p>
	 * <p>If the path start with / or c:/ is the real path of file, otherwise, this must be start without / to root path of application.</p>
	 * <p>Each file in this directory is a PHP file containing an array variable named $msg. The key is the code or message id, the value is translation.<br/>
	 * Example :
	 * </p>
	 * <pre class="brush:php">$msg["home.title"] = "Hello world";<br />
	 * $msg["home.text"] = "News 1, news 2 and news 3";</pre>
	 *
	 *
	 * @Property
	 * @var string
	 */
	private $i18nMessagePath = "resources/";

	/**
	 * Set the language detection
	 *
	 * @Property
	 * @var LanguageDetectionInterface
	 */
	private $languageDetection;

	/**
	 * Store the instance of language
	 * @var array<MessageFileLanguage
	 */
	private $messageFile = [];

	/**
	 *
	 * @param string $i18nMessagePath The path to the directory storing the translations. <p>The directory path should end with a "/".</p><p>If the path start with / or c:/ is the real path of file, otherwise, this must be start without / to root path of application.</p><p>By default this is resources/</p>
	 * @param LanguageDetectionInterface $languageDetection LanguageDetectionInterface
	 */
	public function __construct($i18nMessagePath = "resources/", LanguageDetectionInterface $languageDetection) {
		$this->i18nMessagePath = $i18nMessagePath;
		$this->languageDetection = $languageDetection;
	}

	/**
	 * This function return the real path set in parameter.
	 * @return string
	 */
	private function getPath() {
		if(strpos($this->i18nMessagePath, '/') === 0 || strpos($this->i18nMessagePath, ':/') === 1) {
			return $this->i18nMessagePath;;
		}
		return ROOT_PATH.$this->i18nMessagePath;
	}

	/**
	 * Return the instance of the MessageFileLanguage
	 * Each MessageFileLanguage is link to one language
	 *
	 * @param string $language
	 * @return MessageFileLanguage
	 */
	private function getMessageFile($language) {
		if(!isset($this->messageFile[$language])) {
			$this->messageFile[$language] = new MessageFileLanguage($this->getPath(), $language);
		}
		return $this->messageFile[$language];
	}

	/**
	 * Retrieve the translation of code or message.
	 * Check in the $msg variable if the key exist to return the value. This function check all the custom file if the translation is not in the main message_[language].php
	 * If this message doesn't exist, it return a link to edit it.
	 *
	 */
	public function getTranslation($message, array $parameters = [], LanguageDetectionInterface $languageDetectionInterface = null) {
		if(!$languageDetectionInterface) {
			$lang = $this->languageDetection->getLanguage();
		}
		else {
			$lang = $languageDetectionInterface->getLanguage();
		}
		//Load the main file
		if($this->msg[$lang] === null) {
			$this->retrieveMessages($lang);
		}
		if (isset($this->msg[$lang][$message])) {
			// build a replacement array with braces around the context keys
			$replace = array();
			foreach ($parameters as $key => $val) {
				$replace['{' . $key . '}'] = $val;
			}

			// interpolate replacement values into the message and return
			return strtr($this->msg[$lang][$message], $replace);
		}

		return null;
	}

	/**
	 * Retrieve array variable store in the language file.
	 * This function include the message resource by default and the language file if the language code is set.
	 * The file contain an array with translation, we retrieve it in a private array msg.
	 *
	 * @param string $language Language code
	 * @return boolean
	 */
	private function retrieveMessages($language) {
		$this->msg = array();
		if($language) {
			if (file_exists($this->getPath().'messages_'.$language.'.php')){
				$this->msg[$language] = require_once $this->getPath().'messages_'.$language.'.php';
			}
		}
	}


	/***************************/
	/****** Edition mode *******/
	/***************************/

	/**
	 * The list of all messages in all languages
	 * @var array<string, FineMessageLanguage>
	 */
	private $messages = array();

	public function getAllTranslationByLanguage() {
		$languages = $this->getLanguageList();
		foreach ($languages as $language) {
			$this->getTranslationsForLanguage($language);
		}
		return json_encode($this->messages);
	}

	/**
	 * Return a list of all message for a language.
	 *
	 * @param string $language Language
	 * @return array<string, string> List with key value of translation
	 */
	public function getTranslationsForLanguage($language) {
		if (!isset($this->messages[$language])) {
			$messageLanguage = $this->getMessageFile($language);
			$this->messages[$language] = $messageLanguage->getAllMessages();
		}
		return $this->messages[$language];
	}

	/**
	 * Return a list of all message for a key, by language.
	 *
	 * @param string $key Key of translation
	 * @return array<string, string> List with key value of translation
	 */
	public function getTranslationsForKey($key) {
		$this->getAllTranslationByLanguage();
		$translations = [];
		foreach ($this->messages as $language => $messages) {
			foreach ($messages as $messageKey => $message) {
				if($key == $messageKey) {
					$translations[$language] = $message;
				}
			}
		}
		return $translations;
	}

	/**
	 * Delete a translation for a language. If the language is not set or null, this function deletes the translation for all language.
	 *
	 * @param string $key Key to remove
	 * @param string|null $language Language to remove key or null for all
	 */
	public function deleteTranslation($key, $language = null) {
		if($language === null) {
			$languages = $this->getLanguageList();
		}
		else {
			$languages = array($language);
		}
		foreach ($languages as $language) {
			$messageFile = $this->getMessageFile($language);
			$messageFile->deleteMessage($key);
			$messageFile->save();

			unset($this->messages[$language][$key]);
		}
	}

	/**
	 * Add or change a translation
	 *
	 * @param string $key Key of translation
	 * @param string $value Message of translation
	 * @param string $language Language to add translation
	 */
	public function setTranslation($key, $value, $language) {
		$messageFile = $this->getMessageFile($language);
		$messageFile->setMessage($key, $value);
		$messageFile->save();

		$this->messages[$language][$key] = $value;
	}

	/**
	 * Add or change many translations in one time
	 *
	 * @param array<string, string> $messages List with key value of translation
	 * @param string $language Language to add translation
	 */
	public function setTranslationsForLanguage(array $messages, $language) {
		$messageFile = $this->getMessageFile($language);
		$messageFile->setMessages($messages);
		$messageFile->save();

		$this->messages[$language] = array_merge($this->messages[$language], $messages);
	}

	/**
	 * Add or change many translations in one time
	 *
	 * @param array<string, string> $messages List with key value of translation
	 * @param string $key Key to add translation
	 */
	public function setTranslationsForKey(array $messages, $key) {
		foreach ($messages as $language => $value) {
			$messageFile = $this->getMessageFile($language);
			$messageFile->setMessage($key, $value);
			$messageFile->save();
		}
	}

	/**
	 * List of all languages supported
	 *
	 * @return array<string>
	 */
	public function getLanguageList() {

	}
}

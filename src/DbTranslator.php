<?php
/*
 * Copyright (c) 2012-2015 David NEGRIER
 *
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine\Translator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Mouf\Utils\Cache\CacheInterface;
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
	 * The connection to the database.
	 *
	 * @var Connection
	 */
	private $dbConnection;

	/**
	 * @var CacheInterface
	 */
	private $cacheService;

	/**
	 * Set the language detection
	 *
	 * @var LanguageDetectionInterface
	 */
	private $languageDetection;

	/**
	 * @var Statement
	 */
	private $queryPreparedStatement;

	/**
	 * Default time to live for the cache in seconds.
	 * @var int
	 */
	private $cacheTtl;

	/**
	 *
	 * @param Connection $dbConnection
	 * @param CacheInterface $cacheService
	 * @param LanguageDetectionInterface $languageDetection LanguageDetectionInterface
	 * @param int $cacheTtl
	 */
	public function __construct(Connection $dbConnection, CacheInterface $cacheService, LanguageDetectionInterface $languageDetection, $cacheTtl = 7200) {
		$this->dbConnection = $dbConnection;
		$this->cacheService = $cacheService;
		$this->languageDetection = $languageDetection;
		$this->cacheTtl = $cacheTtl;
	}

	private function getQueryPreparedStatement() {
		if ($this->queryPreparedStatement === null) {
			$this->queryPreparedStatement = $this->dbConnection->prepare("SELECT message FROM message_translations WHERE `msg_key` = :msg_key AND language = :language");
		}
		return $this->queryPreparedStatement;
	}

	/**
	 * Retrieve the translation of code or message.
	 * Check in the $msg variable if the key exist to return the value. This function check all the custom file if the translation is not in the main message_[language].php
	 *
	 */
	public function getTranslation($message, array $parameters = [], LanguageDetectionInterface $languageDetection = null) {
		if(!$languageDetection) {
			$lang = $this->languageDetection->getLanguage();
		} else {
			$lang = $languageDetection->getLanguage();
		}

		$key = 'translate_'.$message.'_'.$lang;
		$translation = $this->cacheService->get($key);

		if (!$translation) {
			$statement = $this->getQueryPreparedStatement();
			$statement->execute([
				'msg_key' => $message,
				'language' => $lang
			]);
			$translation = $statement->fetchColumn(0);

			if ($translation === false) {
				$translation = null;
			}

			$this->cacheService->set($key, $translation, 3600*24);
		}

		if ($translation === null) {
			return null;
		}

		// build a replacement array with braces around the context keys
		$replace = array();
		foreach ($parameters as $pkey => $val) {
			$replace['{' . $pkey . '}'] = $val;
		}

		// interpolate replacement values into the message and return
		return strtr($translation, $replace);
	}

	/***************************/
	/****** Edition mode *******/
	/***************************/


	/**
	 * Return a list of all message for a language.
	 *
	 * @param string $language Language
	 * @return array<string, string> List with key value of translation
	 */
	public function getTranslationsForLanguage($language) {
		$stmt = $this->dbConnection->executeQuery("SELECT msg_key, message FROM message_translations WHERE language = :language",
			[
				"language" => $language
			]);

		$messages = [];
		foreach ($stmt as $row) {
			$messages[$row['msg_key']] = $row['message'];
		}

		return $messages;
	}

	/**
	 * Return a list of all message for a key, by language.
	 *
	 * @param string $key Key of translation
	 * @return array<string, string> List with key value of translation
	 */
	public function getTranslationsForKey($key) {
		$stmt = $this->dbConnection->executeQuery("SELECT language, message FROM message_translations WHERE msg_key = :msg_key",
			[
				"msg_key" => $key
			]);

		$messages = [];
		foreach ($stmt as $row) {
			$messages[$row['language']] = $row['message'];
		}

		return $messages;
	}

	/**
	 * Delete a translation for a language. If the language is not set or null, this function deletes the translation for all language.
	 *
	 * @param string $key Key to remove
	 * @param string|null $language Language to remove key or null for all
	 */
	public function deleteTranslation($key, $language = null) {
		if($language === null) {
			$this->dbConnection->executeUpdate('DELETE FROM message_translations WHERE msg_key = :msg_key', [
				'msg_key' => $key
			]);
		} else {
			$this->dbConnection->executeUpdate('DELETE FROM message_translations WHERE msg_key = :msg_key AND language = :language', [
				'msg_key' => $key,
				'language' => $language,
			]);
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
		$this->dbConnection->executeUpdate('REPLACE INTO message_translations VALUES (:msg_key, :language, :value)', [
			'msg_key' => $key,
			'language' => $language,
			'message' => $value,
		]);
	}

	/**
	 * List of all language supported
	 *
	 * @return array<string>
	 */
	public function getLanguageList() {
		$stmt = $this->dbConnection->executeQuery("SELECT DISTINCT(language) FROM message_translations");

		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return array_map(function($item) { return $item['language']; }, $result);
	}

	/**
	 * Return an array with all the key create without language checking
	 *
	 * @return array<string> All key
	 */
	public function getAllKey() {
		$stmt = $this->dbConnection->executeQuery("SELECT DISTINCT(msg_key) FROM message_translations");

		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return array_map(function($item) { return $item['msg_key']; }, $result);
	}
}

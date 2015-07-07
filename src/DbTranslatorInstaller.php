<?php
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Installer\PackageInstallerInterface;
use Mouf\MoufManager;

/**
 * Mouf Installer for this package
 */
class DbTranslatorInstaller implements PackageInstallerInterface
{

    /**
     * (non-PHPdoc)
     * @see \Mouf\Installer\PackageInstallerInterface::install()
     */
    public static function install(MoufManager $moufManager)
    {
		//FineFileTranslatorService
		if (!$moufManager->instanceExists("dbTranslatorService")) {
			$cascadingLanguageDetection = null;
			if($moufManager->instanceExists('cascadingLanguageDetection')) {
				$cascadingLanguageDetection = $moufManager->getInstanceDescriptor("cascadingLanguageDetection");
			}

      $dbConnectionDescriptor = $moufManager->getInstanceDescriptor('dbalConnection');

			$dbTranslator = $moufManager->createInstance("Mouf\\Utils\\I18n\\Fine\\Translator\\DbTranslator");
			$dbTranslator->setName("dbTranslatorService");
			$dbTranslator->getProperty("dbConnection")->setValue($dbConnectionDescriptor);

			if($cascadingLanguageDetection) {
				$dbTranslator->getProperty("languageDetection")->setValue($cascadingLanguageDetection);
			}

			if($moufManager->instanceExists('defaultTranslationService')) {
				$defaultTranslationService = $moufManager->getInstanceDescriptor("defaultTranslationService");
				$translators = $defaultTranslationService->getProperty('translators')->getValue();
				$translators[] = $dbTranslator;
				$defaultTranslationService->getProperty('translators')->setValue($translators);
			}
		}

		// Let's rewrite the MoufComponents.php file to save the component
		$moufManager->rewriteMouf();

    }
}

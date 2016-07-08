<?php
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Actions\InstallUtils;
use Mouf\Database\Patcher\DatabasePatchInstaller;
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
		// These instances are expected to exist when the installer is run.
		$dbalConnection = $moufManager->getInstanceDescriptor('dbalConnection');
		$apcCacheService = $moufManager->getInstanceDescriptor('apcCacheService');
		$cascadingLanguageDetection = $moufManager->getInstanceDescriptor('cascadingLanguageDetection');

		// Let's create the instances.
		$dbTranslatorService = InstallUtils::getOrCreateInstance('dbTranslatorService', 'Mouf\\Utils\\I18n\\Fine\\Translator\\DbTranslator', $moufManager);

		// Let's bind instances together.
		if (!$dbTranslatorService->getConstructorArgumentProperty('dbConnection')->isValueSet()) {
			$dbTranslatorService->getConstructorArgumentProperty('dbConnection')->setValue($dbalConnection);
		}
		if (!$dbTranslatorService->getConstructorArgumentProperty('cacheService')->isValueSet()) {
			$dbTranslatorService->getConstructorArgumentProperty('cacheService')->setValue($apcCacheService);
		}
		if (!$dbTranslatorService->getConstructorArgumentProperty('languageDetection')->isValueSet()) {
			$dbTranslatorService->getConstructorArgumentProperty('languageDetection')->setValue($cascadingLanguageDetection);
		}

		if (!$moufManager->instanceExists("dbTranslatorService")) {
			if($moufManager->instanceExists('defaultTranslationService')) {
				$defaultTranslationService = $moufManager->getInstanceDescriptor("defaultTranslationService");
				$translators = $defaultTranslationService->getProperty('translators')->getValue();
				$translators[] = $dbTranslatorService;
				$defaultTranslationService->getProperty('translators')->setValue($translators);
			}
		}

		DatabasePatchInstaller::registerPatch($moufManager, "dbTranslatorPatch", "Creates the translation table used by the DB translator",
			"vendor/mouf/utils.i18n.fine.db-translator/sql/up/dbTranslatorPatch.sql");

		// Let's rewrite the MoufComponents.php file to save the component
		$moufManager->rewriteMouf();

    }
}

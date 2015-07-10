<?php
/*
 Copyright (C) 2015 David Négrier - THE CODING MACHINE

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Utils\Cache\InMemoryCache;
use Mouf\Utils\Cache\NoCache;
use Mouf\Utils\I18n\Fine\Language\FixedLanguageDetection;

/**
 */
class DbTranslatorTest extends \PHPUnit_Framework_TestCase {

	protected $dbConnection;

	/**
	 * @var DbTranslator
	 */
	protected $dbTranslator;

	protected function setUp() {

		$config = new \Doctrine\DBAL\Configuration();
		$connectionParams = array(
			//'dbname' => $GLOBALS['db_name'],
			'user' => $GLOBALS['db_username'],
			'password' => $GLOBALS['db_password'],
			'host' => $GLOBALS['db_host'],
			'driver' => 'pdo_mysql',
		);
		$this->dbConnection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

		$this->dbConnection->getSchemaManager()->dropAndCreateDatabase($GLOBALS['db_name']);

		$connectionParams = array(
			'dbname' => $GLOBALS['db_name'],
			'user' => $GLOBALS['db_username'],
			'password' => $GLOBALS['db_password'],
			'host' => $GLOBALS['db_host'],
			'driver' => 'pdo_mysql',
		);
		$this->dbConnection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);



		$sql = file_get_contents(__DIR__.'/../sql/up/dbTranslatorPatch.sql');
		$stmt = $this->dbConnection->prepare($sql);
		$stmt->execute();
		$stmt->closeCursor();

		$this->dbConnection->exec("INSERT INTO message_translations VALUES ('hello', 'en', 'world');");
		$this->dbConnection->exec("INSERT INTO message_translations VALUES ('hello', 'fr', 'monde');");
		$this->dbConnection->exec("INSERT INTO message_translations VALUES ('hop', 'en', 'hop {a} {b}');");
		$this->dbConnection->exec("INSERT INTO message_translations VALUES ('hop', 'fr', 'yop {b} {a}');");

		$this->dbTranslator = new DbTranslator($this->dbConnection, new InMemoryCache(), new FixedLanguageDetection());
	}


	public function testGetMessage() {
		$this->assertEquals('world', $this->dbTranslator->getTranslation('hello'));
		$this->assertEquals('monde', $this->dbTranslator->getTranslation('hello', [], new FixedLanguageDetection('fr')));
		$this->assertEquals('hop aa bb', $this->dbTranslator->getTranslation('hop', [ 'a' => 'aa', 'b' => 'bb' ]));
		$this->assertEquals('yop bb aa', $this->dbTranslator->getTranslation('hop', [ 'a' => 'aa', 'b' => 'bb' ], new FixedLanguageDetection('fr')));
	}

	public function testGetLanguageList() {

		$allLanguages = $this->dbTranslator->getLanguageList();
		$this->assertContains('fr', $allLanguages);
		$this->assertContains('en', $allLanguages);
		$this->assertCount(2, $allLanguages);
	}

	public function testGetTranslationsForKey() {

		$translations = $this->dbTranslator->getTranslationsForKey('hello');
		$this->assertArrayHasKey('fr', $translations);
		$this->assertArrayHasKey('en', $translations);
		$this->assertCount(2, $translations);
	}

	public function testGetTranslationsForLanguage() {

		$translations = $this->dbTranslator->getTranslationsForLanguage('en');
		$this->assertArrayHasKey('hello', $translations);
		$this->assertArrayHasKey('hop', $translations);
		$this->assertCount(2, $translations);
	}

	public function testGetAllKeys() {
		$allKeys = $this->dbTranslator->getAllKey();
		$this->assertContains('hello', $allKeys);
		$this->assertContains('hop', $allKeys);
		$this->assertCount(2, $allKeys);
	}

	public function testSetDelete() {
		$this->assertEquals('world', $this->dbTranslator->getTranslation('hello'));
		$this->dbTranslator->setTranslation('hello', 'the world', 'en');
		$this->assertEquals('the world', $this->dbTranslator->getTranslation('hello'));
		$this->dbTranslator->deleteTranslation('hello', 'en');
		$this->assertNull($this->dbTranslator->getTranslation('hello'));

		$this->dbTranslator->deleteTranslation('hop');
		$this->assertNull($this->dbTranslator->getTranslation('hop'));
		$this->assertNull($this->dbTranslator->getTranslation('hop'), [], new FixedLanguageDetection('fr'));
	}
}

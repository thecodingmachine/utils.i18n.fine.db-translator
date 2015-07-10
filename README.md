Internationalisation with FINE
==============================
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/badges/quality-score.png?b=4.0)](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/?branch=4.0)
[![Code Coverage](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/badges/coverage.png?b=4.0)](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/?branch=4.0)

Fine is a PHP internationalisation package. It will help you develop applications that support several languages. Messages are stored in file.
FINE means: Fine is not English :).

File Translator is performed using PHP mapping files.
This package create one file by language supported.

Dependencies
------------

Fine comes as a *Composer* package and requires the "Mouf" framework to run.
The first step is therefore to [install Mouf](http://www.mouf-php.com/).

Once Mouf is installed, you can process to the Fine installation.

Install Fine
--------------

If you want an easy installation, please add the package utils.i18n.fine.common at the same time in your configuration.
Edit your *composer.json* file, and add a dependency on *mouf/utils.i18n.fine.file-translation.

A minimal *composer.json* file might look like this:
```
	{
	    "require": {
	        "mouf/mouf": "~2.0",
	        "mouf/utils.i18n.fine.file-translator": "4.0.*"
	    },
	    "autoload": {
	        "psr-0": {
	            "Test": "src/"
	        }
	    },
	    "minimum-stability": "dev"
	}
```
As explained above, Fine is a package of the Mouf framework. Mouf allows you (amoung other things) to visualy "build" your project's dependencies and instances.

To install the dependency, run
	php composer.phar install
	

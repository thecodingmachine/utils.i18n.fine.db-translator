FINE: database storage for translations
=======================================
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/badges/quality-score.png?b=4.0)](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/?branch=4.0)
[![Code Coverage](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/badges/coverage.png?b=4.0)](https://scrutinizer-ci.com/g/thecodingmachine/utils.i18n.fine.db-translator/?branch=4.0)

Fine is Mouf's PHP internationalisation package. It will help you develop applications that support several languages.

Most of the time, you will use Fine with the FileTranslator that stores translations in PHP mapping files.
This package contains one *alternative translator* that stores translations in database rather than in files.

Why?
----
Use this **DbTranslator** if you want to make translations editable by your users. You can then directly write 
the translations in the `message_translations` table that is created by this package.

Dependencies
------------

Fine comes as a *Composer* package and requires the "Mouf" framework to run.
The first step is therefore to [install Mouf](http://www.mouf-php.com/).

Once Mouf is installed, you can process to the Fine and DbTranslator installation.

Installation
------------

A typical *composer.json* file might look like this:
```json
	{
	    "require": {
			"mouf/mouf" : "~2.0.0",
			"mouf/utils.i18n.fine.common" : "~4.0",
	    	"mouf/utils.i18n.fine.db-translator" : "~4.0",
	  		"mouf/utils.i18n.fine.manage.bo" : "~4.0"
	    },
	    "minimum-stability": "dev"
	}
```

*mouf/utils.i18n.fine.db-translator* refers to this package and *mouf/utils.i18n.fine.manage.bo* is package that 
contains the user interface in Mouf to view/edit translations. 

To install the dependency, run:

```bash
composer update
```

Now, go to the Mouf UI (http://[your_server]/[your_app]/vendor/mouf/mouf) and process to the package installation by
clicking the "Run installation tasks" button.

You can now edit your translations directly using the "HTML > Fine" menu.

Behind the scenes, the Mouf installer has created a `dbTranslatorService` instance and binded it to the `defaultTranslatorService`.
You can keep using Fine is you would usually do (using the `defaultTranslatorService`) and the `dbTranslatorService`
 will automatically be called.
 
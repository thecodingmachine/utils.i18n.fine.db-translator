Use the translator
==================

Common package
--------------

This package is automatically bound to the cascading translator (this class is in [utils.i18n.fine.common](https://mouf-php.com/packages/mouf/utils.i18n.fine.common) package).

Binding
-------

Bind your dbTranslator in your controller (with Mouf interface) to call the function getTranslation.
In your controller, add a private attribute of TranslatorInterface. This interface forces to have a function getTranslation.

After this you can use it:
```
echo $this->translatorInterface->getTranslation('mykey', array('name', 'myname'));
```

It's possible to add the language detection in parameter, if you want to force another language, but by default the instance use is set in translator.
Example:
```
$languageDetection = new Mouf\Utils\I18n\Fine\Language\FixedLanguageDetection();
$languageDetection->setLanguage('fr');

echo $this->translatorInterface->getTranslation('mykey', array('name', 'myname'), languageDetection);
```

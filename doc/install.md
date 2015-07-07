Fine installation guide
=========================

Install Fine
--------------

By default the installation create a FileTranlator instance named fileTranslatorService. You can add or change this instance with the Mouf interface.

Configuration
-------------

By default the fileTranslatorService creates a default value.
For this example we will add a new instance:
CLick on the menu Instances -> Create a new instance. Add a name for your instance and select FileTranslator
![Fine install screenshot](https://raw.github.com/thecodingmachine/utils.i18n.fine.file-translator/4.0/doc/images/1_create_instance.png)

After you must configure the variable:
languageDetection, this is the function how return the language value to retrieve the great translation, please add it, click on it and drag and drop your languageDetection
![Fine install screenshot](https://raw.github.com/thecodingmachine/utils.i18n.fine.file-translator/4.0/doc/images/2_configure_step_1.png)

The second parameters is the folder, where the files will be save. If you start with / or c:/ the path is the real path on your disk, otherwise if this is a letter, the root path is the application.
CAUTION: You must to check if your web server has the right to write files in this folder.
![Fine install screenshot](https://raw.github.com/thecodingmachine/utils.i18n.fine.file-translator/4.0/doc/images/2_configure_step_2.png)

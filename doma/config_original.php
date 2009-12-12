<?php
  error_reporting(E_ALL & ~E_NOTICE);
  // This file contains the settings for the digital map archive.
  // Each setting contains a name and a value and is presented in the following way:
  //   define('SETTING_NAME', 'setting value');
  // The settning names must not be changed, but the setting values are up to you to edit. All text setting values
  // must be enclosed in single quotes ('). If a setting value contais a single quote, use the \' character combination.
  // Numeric settings and boolean settings (true or false) should not be enclosed in single quotes.

  // *********************************************************************************************************
  //   SYSTEM SETTINGS
  // *********************************************************************************************************

  // User names and passwords.
  // Database login information, ask your database server administrator/web hotel provider if you don't know.
  define('DB_HOST', 'localhost');
  define('DB_USERNAME', 'yourDatabaseUsername');
  define('DB_PASSWORD', 'yourDatabasePassword');
  // The name of the database where the map information is stored. The database must exist prior to creation of the site.
  define('DB_DATABASE_NAME', 'yourDatabaseName');
  // The names of the database tables where user and map information is stored. Do not change unless you have a reason. The database tables must _not_ exist before creation of the site.
  define('DB_MAP_TABLE', 'doma_maps');
  define('DB_SETTING_TABLE', 'doma_settings');
  define('DB_USER_TABLE', 'doma_users');
  define('DB_USER_SETTING_TABLE', 'doma_userSettings');
  define('DB_CATEGORY_TABLE', 'doma_categories');

  // The user name and password for administration (e g adding and editing users).
  define('ADMIN_USERNAME', 'yourAdminUsername');
  define('ADMIN_PASSWORD', 'yourAdminPassword');

  // Path to the map image directory, relative to this file. Don't change unless you have a good reason.
  // The directory is created during creation. Write access to the directory for the server user account under which PHP runs is required.
  define('MAP_IMAGE_PATH', 'map_images');

  // The file that contains the text strings to display on the site.
  // Language files are in xml format and located in the 'languages' directory.
  // You may create your own language file by copying and modifying one of the existing files.
  // Current language files include en.xml (English), sv.xml (Swedish) adn no_nb.xml (Norwegian Bokmål).
  define('LANGUAGE_FILE', 'en.xml');

  // The MySQL text sorting order, known as 'collation'.
  // Use utf8_general_ci for English, utf8_swedish_ci for Swedish, and utf8_danish_ci for Norwegian Bokmål.
  // Other collations can be found at the MySQL website, http://dev.mysql.com
  // NOTE: this setting only has effect when creating the site. Changing this setting after the site has been created will not have any effect.
  define('DB_COLLATION', 'utf8_general_ci');

  // The email address for the administrator of the page.
  // Used as reply address when sending confirmation emails to new users.
  // Please make sure that your server is properly configured for sending emails, more info can be found at http://www.php.net/mail.
  // The email address must be changed from email@yourdomain.com to a valid address, or the sending won't work.
  define('ADMIN_EMAIL', 'email@yourdomain.com');

  // Specifies the code that a person has to enter when creating a new user accounts by himself without any administrator involved.
  // Leave the code empty ('') to prevent people to create user accounts theirselves.
  define('PUBLIC_USER_CREATION_CODE', '');

  // *********************************************************************************************************
  //   APPEARANCE SETTINGS
  // *********************************************************************************************************

  // The name of the site as displayed in the browser's window title when browsing the user list page.
  define('_SITE_TITLE', 'The Digital Orienteering Map Archive');

  // The name of the site as displayed in the browser's window title when browsing the user list page.
  define('_SITE_DESCRIPTION', 'Welcome to the digital orienteering map archive!');

  // Size and scaling of thumbnail images. Don't change unless you have a good reason.
  define('THUMBNAIL_WIDTH', 400);
  define('THUMBNAIL_HEIGHT', 100);
  define('THUMBNAIL_SCALE', 0.5);

  //-------------DOMA 3.0 new settings---------------
  // Path to temporary file storage directory, relative to this file. Don't change unless you have a good reason.
  // The directory is created during creation. Write access to the directory for the server user account under which PHP runs is required.
  define('TEMP_FILE_PATH', 'temp');

  // The Google Maps API key, required to embed overview maps in the site.
  // Acquire your key at http://code.google.com/apis/maps/signup.html
  define('GOOGLE_MAPS_API_KEY', '');
  
  //Show languages in topbar (1 = yes, 0 = no)
  define('LANGUAGES_SHOW','1');
  
  //Available languages separated by |     sample:SE|EN|CZ     language files in folder must look like se.xml, en.xml, cz.xml, ...
  //Flags are stored in /gfx/flag folder in format xx.png
  define('LANGUAGES_AVAILABLE','CZ|EN|SE');
  ?>

Installing the framework
==

There's no installing the framework. There's no daemon or
prerequisites or anything.

There is an example app
--

The example app uses a database. Because it uses a database (and
all applications always will), part of the config is connecting
to a database and `exit()` if that connection fails.

<strike>Which means in order to actually see the example app, you will
have to import the attached MySQL database and change the
config in <u>`config/database.php`</u>.</strike>

The app uses an SQLite 3 database, so an SQLite adapter is required. If
you don't have SQLite, but you do have MySQL, there's a MySQL dump in
here somewhere as well. Configuration should be easy:
<u>`example_app/config/database.php`</u>

The framework uses `APC` if available and doesn't if not.  
No config required.

The framework uses [`PSR-0`](<http://groups.google.com/group/php-standards/web/psr-0-final-proposal>)
for file structure and it's very important that you keep that
structure! The (example) app is freely located anywhere.

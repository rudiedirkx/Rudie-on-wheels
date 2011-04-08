
To do:
------

2. E-mail: Create simple `MailContext` and super simple 'mailer'


Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
1. Define environments and their corresponding 'bootstraps'?
    - HTTP: Config + Model + Controller/Dispatcher + Views + Third-parties? + ?
    - CRON: Config + DB (Model?) + Third-parties? + ?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.


How to:
-------

* Vendors: include unnamespaced classes like phpMarkdownExtra (created namespaced extension that references to global markdown class - what to do with multiple classes like in Zend??)

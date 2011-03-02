
To do:
------

1. Validator: Put some kind of ultra easy form builder in `row\form\Validator` (in `row\Form`?)... And then make the Form validatable?<br><br>
2. E-mail: Create simple PHPMailer (?) implementation<br><br>
4. Misc: Handy (extendable) functions like isDate, isTime, randomString etc<br><br>
8. Database: Implement `database\adapters\SQLite3`
8. Database: Implement `database\adapters\PDOpgSQL`


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

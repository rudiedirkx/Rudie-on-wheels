
To do:
------

2. Vendors: Try [`APC`](http://www.php.net/manual/en/book.apc.php) for Vendor class locations (all those `file_exists`'s and Vendor specific closures can't be efficient).<br><br>
4. Validator: Implement custom error message(s)<br><br>
6. Dispatcher: Implement fallback. How exactly? Per controller or per total? If per total: in what controller?
6. Dispatcher: Implement (optional!) ErrorController. Via catch in index.php or internally? (Internally means the catch could also catch the msising ErrorController (which is good).)<br><br>
8. Database: Implement the SQLite3 adapter
8. Database: Create PostgreSQL `database\Adapter` (as `database\adapters\pgSQL`?)

<br>

_The following is less maintained..._

<br>

Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
1. Define environments and their corresponding 'bootstraps'?
    - HTTP: Config + Model + Controller/Dispatcher + Views + Third-parties? + ?
    - CRON: Config + DB (Model?) + Third-parties? + ?
2. Overall: Decide how loosely coupled all _extensions_ must be:
    - Can the `Views` or `DBAL` or `Model` only be used within the framework?
    - Can the `Controller` only be used in combination with the `Dispatcher`? (So not in a cronjob?)
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.


How to:
-------

* Vendors: include unnamespaced classes like phpMarkdownExtra (created namespaced extension that references to global markdown class - what to do with multiple classes like in Zend??)

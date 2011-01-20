
To do:
------

1. Redo `Dispatcher` (and `Controller`) from `Dispatcher->run` to `Controller->_post_action`
2. Database: Give the `database\Adapter`s more Reflection feats (like `_getPKColumns()`)?
3. Make 'internal apps' (applets?) extendable/usable through normal Controller-proxy (like `app\controllers\dbsecretsController` now extends `row\utils\sandbox\controllers\sandboxController`)
4. Database: Implement the SQLite3 adapter
5. Database: Create PostgreSQL `database\Adapter` (as `database\adapters\pgSQL`?)


Debatables:
-----------

1. Define environments and their corresponding 'bootstraps':
    - HTTP: Config + Model + Controller/Dispatcher + Views + Third-parties? + ?
    - CRON: Config + DB (Model?) + Third-parties? + ?
2. Overall: Decide how loosely coupled all _extensions_ must be:
    - Can the `Views` or `DBAL` or `Model` only be used within the framework?
    - Can the `Controller` only be used in combination with the `Dispatcher`? (So not in a cronjob?)
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.
5. How much should be configurable in `database\Model` (and how much static and how much **easily** extendable)?


How to:
-------

* Make `View::markdown()` available everywhere (statically please, yet configured (in bootstrap?))?
* Make `View->url()` or `Controller->url()` available everywhere?
* LOOSELY couple everything together (dispatcher > controller > database + views)
* Vendors: include unnamespaced classes like phpMarkdownExtra (created namespaced extension that references to global markdown class - what to do with multiple classes like in Zend??)
* Dispatcher: Lose most (?) options
* Dispatcher: Implement 'inArguments' for dispatch methods 'generic' and 'specific'
* Dispatcher: Implement ErrorController (assign exception to template?)
* Models: Test and finish Model class
* Auth: Implement classes:
    - SessionUser (always exists, lives within LoginSession, will most likely have a UserRecord as data object)
    - LoginSession (always at least 1 layer, owner of the validateSession(), login() and logout() methods)
    - ACL (lives within Controller, checked versus SessionUser)
* Views: Implement simple Views with minimal helpers
* Validation: Implement Validator with minimal standard validation rules

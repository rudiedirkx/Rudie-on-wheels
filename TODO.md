
To do:
------

* Database: Implement the SQLite3 adapter


Debatables:
-----------

* Is utils\Options efficient/fast/smart enough? Functionality is perfect, but ... ?
* Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.
* How much should be configurable in database\Model (and how much static and how much **easily** extendable)?


How to:
-------

* Trigger _fill when new Model objects are created? The database\Adapters' fetchObject methods don't call Model::__construct with arguments, so _fill isn't executed...

* LOOSELY couple everything together (dispatcher > controller > database + views)
* Vendors: include unnamespaced classes like phpMarkdownExtra (created namespaced extension that references to global markdown class - what to do with multiple classes like in Zend??)
* Dispatcher: Make sure Controller Actions are checked from **outside** the Controller class, so only public methods can be called
* Dispatcher: Lose most (?) options
* Dispatcher: Just call controller classes BlogController etc (like all other frameworks do)
* Dispatcher: Finalize existing dispatch mathod ('generic')
* Dispatcher: Implement other two dispatch methods ('specific' and 'fallback')
* Dispatcher: Implement 'inArguments' for dispatch methods 'generic' and 'specific'
* Dispatcher: Implement ErrorController (assign exception to template?)
* Models: Test and finish Model class
* Auth: Implement classes:
    - SessionUser (always exists, lives within LoginSession, will most likely have a UserRecord as data object)
    - LoginSession (always at least 1 layer, owner of the validateSession(), login() and logout() methods)
    - ACL (lives within Controller, checked versus SessionUser)
* Views: Implement simple Views with minimal helpers
* Validation: Implement Validator with minimal standard validation rules


How to:
-------

* Database: Implement the $params for some db methods (in adapters as well as Model)
* Database: Implement the SQLite3 adapter

* LOOSELY couple everything together (dispatcher > controller > database + views)

* Dispatcher: Make sure Controller Actions are checked from **outside** the Controller class, so only public methods can be called
* Dispatcher: Lose most (?) options
* Dispatcher: Just call controller classes BlogController etc (like all other frameworks do)
* Dispatcher: Finalize existing dispatch mathod ('generic')
* Dispatcher: Implement other two dispatch methods ('specific' and 'fallback')
* Dispatcher: Implement 'inArguments' for dispatch methods 'generic' and 'specific'
* Dispatcher: Implement ErrorController (assign exception to template?)
* Models: Test and finish Model class
* Auth: Implement classes:
** SessionUser (always exists, lives within LoginSession, will most likely have a UserRecord as data object)
** LoginSession (always at least 1 layer, owner of the validateSession(), login() and logout() methods)
** ACL (lives within Controller, checked versus SessionUser)
* Views: Implement simple Views with minimal helpers
* Validation: Implement Validator with minimal standard validation rules

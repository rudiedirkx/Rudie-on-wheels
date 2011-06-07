
To do:
------

### For 1.0

1. Create 'internal redirect' from `Controller` eg. `$this->_internal(array('controller' => 'oele', 'action' => 'boele'))` or `$this->_internal('go/here/15')`
1. Implement error/404 `Controller Action` via `Dispatcher` 'error handler'

1. `Model`/`Adapter`: `replaceholders` for full queries too? So move the replacing to further on?

1. `Dispatcher`: Detect Action arguments automatically and throw 404 if required arguments aren't present

1. E-mail: Create simple `MailContext` and super simple 'mailer'

### For 1.1

1. Perfect mixins
2. Make the entire thing more `RESTful` (OMG!? Am I using it now too??)

Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.

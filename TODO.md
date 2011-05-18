
To do:
------

### For 1.0

0. Fix Chain: every time a chain is called to exec a native method, an event for that method is added: NOT GOOD. Must be added only ONCE per class lifetime (not per object lifetime and definitely not per call)
1. E-mail: Create simple `MailContext` and super simple 'mailer'
2. Create `internal redirect` from `Controller` eg. `$this->_internal(array('controller' => 'oele', 'action' => 'boele'))` or `$this->_internal('go/here/15')`
3. Implement error/404 `Controller Action` via `Dispatcher` 'error handler'
4. `Model`/`Adapter`: `replaceholders` for full queries too? So move the replacing to further on?

### For 1.1

1. Perfect mixins
2. Make the entire thing more `RESTful` (OMG!? Am I using it now too??)

Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.

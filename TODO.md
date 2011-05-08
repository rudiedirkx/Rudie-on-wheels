
To do:
------

### For 1.0

1. E-mail: Create simple `MailContext` and super simple 'mailer'<br><br>
2. Create `internal redirect` from `Controller` eg. `$this->_internal(array('controller' => 'oele', 'action' => 'boele'))` or `$this->_internal('go/here/15')`
3. Implement error/404 `Controller Action` via `Dispatcher` 'error handler'<br><br>
4. Make the entire thing more `RESTful` (OMG!? Am I using it now too??)

### For 1.1

1. Implement mixins/events (like `li3` but **much** simpler (?))

Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.

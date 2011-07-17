
To do:
------

### Must have

1. Better `SessionUser::env_IP()`
1. Better `SessionUser::env_UA()`?
1. Better `SessionUser::env_Domain()`
1. Check ALL row classes for extendability: **never** must a `ROW` class name be hardcoded in `ROW`! Give important classes a static `$class` property?

### For 1.2

1. Implement error/404 `Controller Action` via `Dispatcher` 'error handler'
1. Perfect mixins
2. Make the entire thing more `RESTful` (OMG!? Am I using it now too??)

Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.

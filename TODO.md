
To do:
------

2. E-mail: Create simple `MailContext` and super simple 'mailer'
1. `SimpleForm`: implement default values (`SimpleForm->render($defaultValues)`) (`SimpleForm->elementValue($name)`)
1. `SimpleForm`: implement element `type` 'grid' / 'matrix'
1. `SimpleForm`: implement `pre_validate` and `post_validate` events
1. `SimpleForm`: implement `SimpleForm->context`


Debatables:
-----------

1. Am I being inconsistent with my underscores and/or static function naming?
3. Is `utils\Options` efficient/fast/smart enough? Functionality is perfect, but ... ?
4. Are the database\Adapter method names sensible? E.g. `selectFieldsNumeric` and `selectFieldsAssoc`.

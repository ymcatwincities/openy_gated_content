## About
Some text here...

### Override base fields
If your users data have different fields, you can use
hook_entity_base_field_info_alter to add them to AuthCustomUser entity.

Example:

```php
function hook_entity_base_field_info_alter(&$fields, $entity_type) {

  // Alter the mymodule_text field to use a custom class.
  if ($entity_type->id() == 'gc_auth_custom_user') {
    $fields['password'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Password'))
      ->setDescription(t('User password.'));
  }
}

```

### How to Modify migration
For this you can change content of config
migrate_plus.migration.gc_auth_custom_users.yml. You can modify
destination fields and processors according to your needs.


### How to Run migration

From UI:
- Login as admin
- Go to /admin/openy/openy-gc-auth/settings/provider/custom/import_csv
- Select migration group to run
- Run batch

From drush:
```shell script
drush mim gc_auth_custom_users
```

Status check:
```shell script
drush ms
```

### How to upload source file

You can upload source file directly t0 private://gc_auth/import
File name should be gc_auth_custom_users.csv

Or you can upload file from UI in migration group settings, for example:
- /admin/structure/migrate/manage/gc_auth

## Notes:

Current Open Y version contains hardcoded module migrate_source_csv v2.2,
so migrate_plus.migration.gc_auth_custom_users.yml supports 2.x version.
When we will update to 3.x, this config should be updated according to:
- https://www.drupal.org/node/3060246

CSV Limit source plugin and Migration group edit form extension to allow
CSV file upload was re-used from:
- https://github.com/skilld-labs/skilld_migrate

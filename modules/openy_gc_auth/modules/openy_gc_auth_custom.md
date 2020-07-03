# README

## About

Some text here...

### Override base fields

If your users' data have different fields, you can use
hook\_entity\_base\_field\_info\_alter to add them to AuthCustomUser entity.

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

For this, you can change the content of config
migrate\_plus.migration.gc\_auth\_custom\_users.yml.
You can modify destination fields and processors according to your needs.

### How to Run the migration

From UI:

* Login as admin
* Go to /admin/openy/openy-gc-auth/settings/provider/custom/import\_csv
* Select migration group to run
* Run batch

From drush: \`\`\`shell script drush mim gc\_auth\_custom\_users

```text
Status check:
```shell script
drush ms
```

### How to upload a source file

You can upload source file directly t0 private://gc\_auth/import
File name should be gc\_auth\_custom\_users.csv

Or you can upload file from UI in migration group settings, for example:

* /admin/structure/migrate/manage/gc\_auth

## Notes:

Current Open Y version contains hardcoded module migrate\_source\_csv v2.2,
so migrate\_plus.migration.gc\_auth\_custom\_users.yml supports 2.x version.
When we will update to 3.x, this config should be updated according to:

* [https://www.drupal.org/node/3060246](https://www.drupal.org/node/3060246)

CSV Limit source plugin and Migration group edit form extension
to allow CSV file upload was re-used from:

* [https://github.com/skilld-labs/skilld\_migrate](https://github.com/skilld-labs/skilld_migrate)


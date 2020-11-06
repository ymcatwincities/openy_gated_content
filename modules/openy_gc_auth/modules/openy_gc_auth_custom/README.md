## About

This module provides custom authentication for Virtual Y.
You can use any source of data, it just should be exported to CSV file.

This is how default structure of CSV looks like:

```csv
Member ID,Member First Name,Primary Member,Member Email,Package Name,Package Site
309302,Test,Yes,TEST@GMAIL.COM,OLY - Family - Staff,Oscar Lasko Branch
309303,Jon,No,JON@GMAIL.COM,OLY - Family - Staff,Oscar Lasko Branch
117892,Doe,Yes,Doe@GMAIL.COM,West Chester - SilverSneakers - S & PP,West Chester
91896,Mike,No,SUPERMIKE@GMAIL.COM,Kennett - Family - Full,Kennett Branch
```

If you have different structure or set of fields, you need to:
- Override base fields for gc_auth_custom_user entity
- Modify migration
- Modify validation logic for frontend application

### How to override base fields
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

### How to add custom validation logic for frontend app

Alter `VirtualYCustomLoginForm` or subscribe on
`gated_content_events_user_login` event.

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

If you need to delete destination records that do not exist in the source
use --sync option:

```shell script
drush mim gc_auth_custom_users --sync=TRUE
```

### How to upload source file

You can upload source file directly to `private://gc_auth/import/gc_auth_custom_users.csv`.

OR

You can upload file from the Drupal UI in Migration Group settings at `/admin/structure/migrate/manage/gc_auth`. If you upload a file from the UI it must be entitled `gc_auth_custom_users.csv` exactly.

## Notes:

### Migrate source CSV 2.x vs 3.x

Current Open Y version contains hardcoded module migrate_source_csv v2.2,
so migrate_plus.migration.gc_auth_custom_users.yml supports 2.x version.
When we will update to 3.x, this config should be updated according to:
- https://www.drupal.org/node/3060246

CSV Limit source plugin and Migration group edit form extension to allow
CSV file upload was re-used from:
- https://github.com/skilld-labs/skilld_migrate

### Migrate tools --sync and Drush 8

If you use Drush 8, migrate tools doesn't support sync option.
See https://www.drupal.org/project/migrate_tools/issues/2809433#comment-13362844

To fix this - apply a patch for migrate_tools
patches/migrate_tools_sync_option_for_drush8.patch

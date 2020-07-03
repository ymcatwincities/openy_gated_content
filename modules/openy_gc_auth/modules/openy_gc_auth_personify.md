# Open Y Gated Content Personify SSO Auth.

Provides Open Y Gated Content Personify SSO authentication provider
based on Personify module.

## Integration with Personify

Add Personify credentials to your settings.php file:

```text
# Personify SSO data.
$config['personify.settings']['prod_wsdl'] = '';
$config['personify.settings']['stage_wsdl'] = '';
$config['personify.settings']['vendor_id'] = '';
$config['personify.settings']['vendor_username'] = '';
$config['personify.settings']['vendor_password'] = '';
$config['personify.settings']['vendor_block'] = '';

# Personify Prod endpoint.
$config['personify.settings']['prod_endpoint'] = '';
$config['personify.settings']['prod_username'] = '';
$config['personify.settings']['prod_password'] = '';

# Personify Stage endpoint.
$config['personify.settings']['stage_endpoint'] = '';
$config['personify.settings']['stage_username'] = '';
$config['personify.settings']['stage_password'] = '';
```

## Additional configuration

To be abe to login with Personify you have to add to your settings.php
file login URL for stage and prod environments:

```text
# Personify login URL.
$config['openy_gc_auth_personify.settings']['prod_url_login'] = '';
$config['openy_gc_auth_personify.settings']['stage_url_login'] = '';
```

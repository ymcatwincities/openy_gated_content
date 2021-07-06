## About
The goal of this module is to give ability
to log into Vitrual Y, in case if your CRM is Daxko.
It consists of Daxko SSO plugin and
requre's https://github.com/ymcatwincities/daxko_sso.

## How to use this integration.

1. Enable this module.
2. Setup your Daxko SSO credentials
here: /admin/openy/integrations/daxko/daxko-sso
3. Setup link to the page, where Virtual Y
app is installed: /admin/openy/virtual-ymca/gc-auth-settings/provider/daxkosso

4. Save your settings and verify that Daxko was
able to register Virtual Y link from your website at it's settings.
5. Set Daxko SSO as your main authorization plugin
at the Virtual YMCA settings: /admin/openy/virtual-ymca/gc-auth-settings

## I need help.
In case, if you need help, please write your question
at the #developers channel at Open Y slack.

### Forgot Password/Sign Up links are not working as expected

If these links on the Daxko SSO login page are not going to Daxko and are going back to Drupal with an error like `{"error":1,"message":"Wrong cross site check"}` you may need to re-save your GC auth settings. Visit `/admin/openy/virtual-ymca/gc-auth-settings/provider/daxkosso` and just click `Save`.

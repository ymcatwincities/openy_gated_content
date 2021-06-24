## About
This module provides a Gated Content Authentication Provider for the Daxko
 Barcode integration.

## How to use this integration.

1. Enable this module.
2. OPTIONAL (but highly recommended): configure reCaptcha settings at
`/admin/config/services/simple_recaptcha`. Add the
`openy_gc_auth_daxko_barcode_login_form` to the `Form IDs` field values.
3. Add your validation secret and form url and check help messages at
`/admin/openy/virtual-ymca/gc-auth-settings/provider/daxkobarcode`.
4. Save your settings.
5. Set Daxko Barcode as your main authorization plugin
at the Virtual YMCA settings: `/admin/openy/openy-gc-auth/settings`.

## I need help.
In case you need help, please write your question in the #developers channel
 on the Open Y slack.

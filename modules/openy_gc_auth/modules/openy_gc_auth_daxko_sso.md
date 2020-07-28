# README

## About

The goal of this module is to give the ability to log into Virtual Y,
in case if your CRM is Daxko.
It consists of Daxko SSO plugin and requires
[https://github.com/ymcatwincities/daxko\_sso][1].


## How to use this integration.

1. Enable this module.
2. Setup your Daxko SSO credentials

   here: /admin/openy/integrations/daxko/daxko-sso

3. Setup link to the page, where Virtual Y

   app is installed: /admin/openy/openy-gc-auth/settings/provider/daxkosso

4. Save your settings and verify that Daxko was

   able to register Virtual Y link from your website at it's settings.

5. Set Daxko SSO as your main authorization plugin

   at the Virtual Y Content settings: /admin/openy/openy-gc-auth/settings

## I need help.

In case, if you need help, please write your question at
the \#developers channel at Open Y slack.

[1]: https://github.com/ymcatwincities/daxko_sso

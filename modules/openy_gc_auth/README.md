# Virtual Y Authentication plugins.

Virtual Y provide's pluginable system for authentication options.
Goal of every plugin is to check user entered data at CRM and register Drupal user object in the system and authenticate
Virtual Y user as Drupal user. Virtual Y user cannot use drupal login/register/password reset pages.
We've added this restrictions for better security of this module.

## Virtual Y User Segmentation

Since version 1.0, Virtual Y has ability to construct segmentation for your content.
For example, you could show premium content for users that have Virtual Y Premium role.
You can add as many `virtual_y_*` to your setup roles and provide access to different content based on segmentation by roles.

Each plugin by default adds virtual_y role to the user. If you want to organise automatic segmentation of the content, please use
`gated_content_events_user_login` event at your custom module.
Your EventSubscriber could add custom logic based on your user object data that comes from CRM and assign Virtual Y Premium (or other) to Virtual Y user.

You could easily implement your own integration with your CRM if it doesent exist yet.
At now, we have implemented next authentication plugins:

## [Dummy](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_example)

Demo or bypass plugin. User could enter your gated area by clicking on Enter Virtual YMCA button.
Could be used for testing of your Virtual Y or for demonstration of Virtual Y features.
Virtual Y creates real Drupal user after each successful login to the system based on his/her ip address as username.

## [Custom](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_custom)

Virtual Y user could enter gated content area by entering email address.
For better security Recaptcha and emaill link approval features could be enabled at plugin settings form.
This module could be used for integration with the CRM's that dont have authentication plugin implemented or dont support SSO.
For example: Active.net.
This module has demo migration. You can use it for automatic sync of users from your CRM or customers database to Open Y.

## [Daxko SSO](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_daxko_sso)

Virtual Y user could enter Virtual Y using his Daxko credentials. This module require's Daxko Engage product to be enabled at your account.

## [Daxko Barcode](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_daxko_barcode)

Lightweight check for Daxko based on user entered barcode.

## [Personify](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_personify)

Virtual Y user could enter Virtual Y using Personify SSO.

## [Reclique SSO](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_reclique_sso)

Reclique CRM authentication. Users can log in into Virtual Y using their Reclique credentials

## [Avocado SSO](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_avocado_sso)

Avocado CRM authentication. Users can log in into Virtual Y using their Avocado credentials.
***Avocado CRM have issues with blocked users authentication. Please check it before using at production***

## [Y-USA](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_yusa)

Y-USA National Database authentication plugin. This is not a SSO solution. It is similar to Custom auth provider.

# Automated welcome email for new users

Virtual Y Base module allows sending welcome email messages to the client after user successfully logs into Virtual Y for the first time.
Welcome email will not be sent again if a user authenticates on a different device or browser later.
The email, which will be used to send the message, depends on the authentication plugin used in the system: so the
authentication providers which aren't storing the user's email (Daxko Barcode, Y-USA) will not allow to use this feature.

**Please, check your mail system before using this feature.**

This module uses default Drupal mail sending functions to send emails.

You can enable this feature or change message template at setting form at this URI: `/admin/openy/virtual-ymca/welcome-email-settings`

Editing message settings, keep in mind that the user's data you can use in the templates will reflect the data stored
on the side of your Drupal site -- so the user display name can contain some IDs, added by the authentication plugin to
ensure uniqueness over the site.

You can test this feature at this URI: `/admin/openy/virtual-ymca/welcome-email-settings/test`

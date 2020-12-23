# About

This module gives you an ability to use Y-USA national membership database as source for your membership listing.
It works very similar to [custom](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_custom) authentication plugin.

You can verify your members by asking them to enter `email` or `barcode id` or `membership id`.

You can enable recaptcha and email verification as additional checks for your user.

You have to get next data for successful configuration of this auth plugin:

- you association id at y-usa national db;
- verification url (if it differs for your y);
- api login;
- api password;

Y-USA authentication plugin gives you an ability to configure user segmentation logic from admin interface.
You have to connect titles of your user memberships from Y-USA database to the Virtual Y roles.
For example:

``
INDIVIDUAL: Virtual Y
``

``
FAMILY: Virtual Y Premium
``

System will automatically set proper role (user segment) for entered user based on his membership package value from Y-USA database.
Permissions mapping field is multiple. You can add as many pairs as you wish.

For support in connecting your Virtual Y site to the Nationwide Membership database, please contact [ycloud@ymca.net](mailto:ycloud@ymca.net)

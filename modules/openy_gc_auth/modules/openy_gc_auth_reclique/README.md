## About

## This module is outdated! Please use Reclique SSO module for this CRM. 
Link to the new moodule [Reclique SSO](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_reclique_sso)

This module provides [ReClique CRM](https://reclique.com/) authentication plugin for Virtual Y.

It works very similar to [custom](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_custom) authentication plugin.

You can enable recapthca and email verification as additional checks for your user.

You have to get next data for successful configuration of this auth plugin:

- verification url (similar to: https://YOUR_Y_ID.recliquecore.com/api/v1/members/virtual_y/);
- api login;
- api password;

Reclique authentication plugin give's you an ability to configure user segmentation logic from admin interface.
You have to connect titles of your user memberships from Y-USA database to the Virtual Y roles.
For example:

``
Branch Individual: Virtual Y
``

``
Branch Premium: Virtual Y Premium
``

System will automatically set proper role (user segment) for entered user based on his membership package value from Y-USA database.
Permissions mapping field is multiple. You can add as many pairs as you wish.

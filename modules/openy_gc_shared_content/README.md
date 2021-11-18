# Initial connection to the Shared Content server

During the installation of this module, Virtual Y creates a new and fresh connection to the centralized shared content server.
Shared content admins should approve that your request is valid and give you access to the centralized database.

# Reset of the Shared Content Server connection

Sometimes your Virtual Y site may stop communicating with the share content server. This may be due to the secret key for your site becoming out of sync with the server. In this case a complete reset of the connection could help fix this problem.

Steps to reset the connection.

1. Go to `/admin/virtual-y/shared-content/server`.
2. Delete the existing connection to the Open Y Shared Server.
3. Create a new connection at `/admin/virtual-y/shared-content/server/add`. 
4. Use `https://shared.openy.org` as connection domain.
5. Use any name as label.
6. Save the new connection.
7. Email `info@openymca.org` with the URL of your site requesting that they approve the new connection.

# Developer notes

This module provides connection functionality that allows any VY instance to get access to the centralized shared media database.
It provides new entity type for connection and set of different SharedContentSourceType plugins that allows to download parts of the content to Virtual Y.

### SharedContentSourceType plugin:
 - Json API query_args (teaser/full) - for list and individual
 - entity_type (Eg 'node')
 - bundle  (Eg 'gc_video')
 - formatItem(teaser/full) - for listing and preview
 - Create item with all references logic
 - Move similar functions to base plugin


 ### Source Security:
 - Json API Close for anonyms
 - Json API Allow for logged-in users
 - Json API Allow access by token

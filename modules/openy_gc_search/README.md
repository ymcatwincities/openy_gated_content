# Open Y Virtual YMCA Search

The search functionality is based on the Drupal Search API and the search configuration for Open Y
(see `openy_search_api` module).

The module provides modifications for the search index to include the Virtual Y entities to the content,
indexed for the search, and also altering the search view title output to wrap the entity title with a
link to the correspondent Virtual Y page.

For the case your site has custom search you'll need to make sure you've included Virtual Y entity types
and bundles to the datasources configs, as well as configured view modes for them in the specific index
fields (like `rendered_output`), and also made needed changes in the enabled processors' configs.
You can use the `openy_gc_search.install` as an example of such changes.

Other part of the module, an implementation of the `hook_preprocess_views_view_field` makes title
altering -- to provide the right link for the Virtual Y entities. For the case of custom search
configuration on your project you'll need to implement this hook on your custom module, or create
a patch for the current one -- to alter the output in a right way on the search view and its field
you need.

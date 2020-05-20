# Open Y Gated content

## Development

This is just a set of drupal modules. Install it as you would do with any drupal module.

In development purposes, you might want a set of modules to be enabled. Use `openy_gc_demo` module for such purposes. Specify modules that you want to be enabled as dependencies of this module. The CI configuration automatially enables this modules, and all its dependencies.

### Coding standards

#### PHPCS

Please find the PHPCS configuration in `.phpcs.xml`.

In order to use the configuration just run `phpcs` within the module directory.

#### eslint

Please find the esling configuration in `js/gated-content/.eslintrc.js`

To run the check execute `npm install && npm lint-no-fix`. See `js/gated-content/package.json` for details.

Automatic code fixing is available with `npm lint`.

### With docksal for openy

The docksal configuration https://github.com/fivejars/docksal-for-openy/tree/openy-gc-builds is used to for building the PR builds.

In order to use it you have to install Docksal.

Follow the instruction below to get the working local environment that provides 4 local websites (1 for each base theme and not yet installed Open Y):

```
mkdir vymca
cd vymca
git clone --branch openy-gc-builds git@github.com:fivejars/docksal-for-openy.git .docksal
git clone git@github.com:fivejars/openy_gated_content.git
mkdir -p docroot/libraries docroot/sites/default/config/staging
wget -N https://raw.githubusercontent.com/fivejars/openy-project/8.2.x-gated-content-ci/composer.json
docker volume create --name=composer_cache
fin init
```

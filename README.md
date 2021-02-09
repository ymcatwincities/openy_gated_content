# Virtual Y

Virtual Y is a package for Open Y and consists of features, needed to organize gated content for Y clients.

Active development is currently happening at [fivejars/openy_gated_content](https://github.com/fivejars/openy_gated_content). Pull requests should be submitted there. They will be pulled into this repo as they are reviewed and released.

## Server requirements
In order to work Virtual Y requires HTTP server to allow 
- GET
- POST
- DELETE
http methods.

## Submodules

1. [Authentication system](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_auth) - pluginable system for different authentication types.
2. [Demo](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_demo) - set of migrations that installs Demo content at the initial step of your Virtual Y
3. [Storage](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_storage) - set of entities, needed for Virtual Y
4. [Log](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_log) Features for tracking activities of your Virtual Y users.
5. [Shared content](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_shared_content) - module that give's you ability to download content from shared network.
6. [Shared content server](https://github.com/ymcatwincities/openy_gated_content/tree/master/modules/openy_gc_shared_content_server) - module for shared.openy.org server.

## Installation

See also the [video version of these instructions](https://youtu.be/vlqv4ly3iak). This assumes you've already [built an Open Y site](https://github.com/ymcatwincities/openy-project#installation) and have it
 running.

- Add this module to your codebase.
  - via composer: `composer require ymcatwincities/openy_gated_content`
  - without composer: this is not recommended.
- Enable the modules
   - Through the UI:
     - Visit **Extend** in your toolbar.
     - Check "Virtual Y Base", "Open Y Virtual YMCA Storage", and "Open Y
      Virtual YMCA Auth Example".
      - Install and say "yes" to add all required dependencies.
   - Via drush: `drush en openy_gated_content
    openy_gc_auth_example -y`
- Add at least one term in these vocabularies via **Structure** > **Taxonomies**
  - Virtual Y Category
  - Virtual Y Equipment
  - Virtual Y Level
- Create a **Landing Page** and add the **Virtual Y Content** paragraph to the
 **Content Area** section.
- From the toolbar go to **Virtual Y** > **Videos** > **Add Video** and add a
 piece of video content.
- Visit the landing page you created, click to log in, and observe your new
 Virtual Y.

## Development

In development purposes, you might want a set of modules to be enabled. Use
`openy_gc_demo` module for such purposes. Specify modules that you want to be
enabled as dependencies of this module. The CI configuration automatially
enables this modules, and all its dependencies.

### Coding standards

#### PHPCS

Please find the PHPCS configuration in `.phpcs.xml`.

In order to use the configuration just run `phpcs` within the module directory.

You can add this script to .git/hooks/pre-commit to run phpcs and phpcbf on `git commit`
```shell script
PROJECT=`php -r "echo dirname(dirname(dirname(realpath('$0'))));"`
cd $PROJECT
echo "Running phpcs and phpcbf..."
phpcs .
if [ $? != 0 ]
then
    phpcbf .
    exit 1
fi
```

#### eslint

Please find the esling configuration in `js/gated-content/.eslintrc.js`

To run the check execute `npm install && npm lint-no-fix`. See
`js/gated-content/package.json` for details.

Automatic code fixing is available with `npm lint`.

### With docksal for openy

The docksal configuration
https://github.com/fivejars/docksal-for-openy/tree/openy-gc-builds is used to
for building the PR builds.

In order to use it you have to install Docksal.

Follow the instruction below to get the working local environment that provides
4 local websites (1 for each base theme and not yet installed Open Y):

```shell script
mkdir vymca
cd vymca
git clone --branch openy-gc-builds \
  git@github.com:fivejars/docksal-for-openy.git .docksal
git clone git@github.com:fivejars/openy_gated_content.git
mkdir -p docroot/libraries docroot/sites/default/config/staging
wget -N https://raw.githubusercontent.com/fivejars/openy-project/8.2.x-gated-content-ci/composer.json
docker volume create --name=composer_cache
fin init
```

### Vimeo private videos

For videos, protected from embed by "Specific domains" you can have an issue
with thumbnails download to drupal media. In this case - apply a patch
for drupal core:

* _patches/OEmbed\_vimeo\_private\_videos.patch_ - in case of using core media
* _patches/video\_embed\_field\_vimeo\_private\_videos.patch_ - in case of
using video_embed_field module


### JSON API patch required for Drupal 8.7

```json
{
  "extra": {
    "patches": {
      "drupal/core": {
        "JSONAPI wont install (8.7-specific)": "https://www.drupal.org/files/issues/2019-05-23/jsonapi_2996114.patch"
      }
    }
  }
}
```

### Migration notes
If you have error:
```
TypeError: Argument 6 passed to __construct() must be an instance of EntityTypeManagerInterface
```
apply patch to composer.json:
```json
{
  "extra": {
    "patches": {
      "drupal/paragraphs": {
        "3079627": "https://www.drupal.org/files/issues/2019-09-06/3079627-4.paragraphs.Argument-6-passed-to-construct.patch"
      }
    }
  }
}
```


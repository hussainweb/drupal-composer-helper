# drupal-composer-helper

[![Latest Version](https://img.shields.io/github/release/hussainweb/drupal-composer-helper/all.svg?style=flat-square)](https://github.com/hussainweb/drupal-composer-helper/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/hussainweb/drupal-composer-helper/master.svg?style=flat-square)](https://travis-ci.org/hussainweb/drupal-composer-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/hussainweb/drupal-composer-helper.svg?style=flat-square)](https://packagist.org/packages/hussainweb/drupal-composer-helper)

This plugin handles common operations for composer based Drupal setups. The code in this plugin is derived from the Drupal code base itself and also other projects such as [drupal-composer/drupal-project](https://github.com/drupal-composer/drupal-project).

## Why?

The motive behind writing this plugin was to make a Drupal composer based setup more maintainable. It is easy to start with the template provided by [drupal-composer/drupal-project](https://github.com/drupal-composer/drupal-project) but upgrading it is difficult. The plugin solves that problem by keeping all the code outside your project and in the plugin which would make upgrading as easy as running `composer update`.

## Installation

If you are starting off a new Drupal 7 or 8 website, it is easier to start with the [hussainweb/drupal-composer-init](https://github.com/hussainweb/drupal-composer-init) composer plugin. Follow the instructions there to install and use the command. The [composer.json files generated](https://gist.github.com/hussainweb/78c0a5fe45556c265b16e10928b76723) include this plugin.

For existing composer setups, just run this command:

```
composer require hussainweb/drupal-composer-helper:^1.0
```

### Usage with the new composer template

_TODO_

### Usage in drupal-composer template

This plugin includes most of the functionality from drupal-composer template. That said, the plugin can be used in the template with some changes. The changes are not necessary to run the plugin but some operations could run twice.

Follow these steps to use the plugin in drupal-composer template:

* Include the plugin: `composer require hussainweb/drupal-composer-helper:~1.0`
* Remove `'DrupalProject\\composer\\ScriptHandler::createRequiredFiles'` from `post-install-cmd` and `post-update-cmd` in `scripts` section of your composer.json. The plugin creates all necessary files.
* (Optional) After the above changes, you may remove `scripts/composer/ScriptHandler.php` file entirely (if you have not made any changes) and remove any references from the composer.json. There are some references in the `autoload` section, and also in `pre-install-cmd` and `post-install-cmd` scripts. This functionality is not present in the plugin but it is not really necessary as it is just a version check for composer.
* (Optional) If you have not changed any paths in `installer-paths` section from `extra` section in your `composer.json`, you may remove them. The plugin sets defaults which match the defaults set by the template. The paths in this section will take precedence over the plugin defaults.

## Configuration

The plugin provides following configuration options (and defaults) in `composer.json` file:

```
{
    ...
    "extra": {
        "drupal-composer-helper": {
            "set-d7-paths": false
        },
        ...
    },
    ...
```

### Drupal Path

This plugin finds the path to Drupal installation using the new setting provided by the new [core Drupal scaffold plugin](https://packagist.org/packages/drupal/core-composer-scaffold). It uses this as a prefix to set all the Drupal relevant installer paths so that Drupal core, modules, and themes may be installed in the correct location. The plugin also runs scaffolding within this directory and creates other required files for Drupal.

Example:
```
{
    ...
    "extra": {
        "drupal-scaffold": {
            "locations: {
                "web-root": "web/"
            }
        },
        "drupal-composer-helper": {
            "additional-cleanup": [],
            "set-d7-paths": false
        },
        ...
    },
    ...
```

For a Drupal 8 setup, the defaults set by this plugin are as follows:
```
'core': 'web/core/',
'module': 'web/modules/contrib/{$name}/',
'theme': 'web/themes/contrib/{$name}/',
'library': 'web/libraries/{$name}/',
'profile': 'web/profiles/contrib/{$name}/',
'drush': 'drush/{$name}/',
'custom-theme': 'web/themes/custom/{$name}/',
'custom-module': 'web/modules/custom/{$name}/',
```

The above are just defaults and can be overridden by the usual `installer-paths` property in `extra` section in your composer.json file.


### drupal-web-dir _(deprecated)_

Default: 'web'

This setting is deprecated in favour of [core's Drupal scaffold plugin settings](#drupal-path) setting. The plugin still falls back to this setting if that is not set but for compatibility with the rest of the Drupal ecosystem, it is a good idea to use the new setting.

### web-prefix _(deprecated)_

Default: 'web'

This setting is deprecated in favour of [core's Drupal scaffold plugin settings](#drupal-path) setting. The plugin still falls back to this setting if that is not set but for compatibility with the rest of the Drupal ecosystem, it is a good idea to use the new setting.

See also: `set-d7-paths`.

### additional-cleanup _(removed)_

Default: Empty array

This has been replaced by the functionality in [`drupal/core-vendor-hardening`](https://packagist.org/packages/drupal/core-vendor-hardening). You still need to explicitly require that plugin in your composer.json. The plugin only suggests adding it.

### set-d7-paths

Default: false

Set this to `true` if you are building a Drupal 7 based site. This configuration option changes the default `installer-paths` paths to Drupal 7 typical paths.

If your `web-prefix` is `docroot`, the `installer-paths` set if this option is `true` are as follows.
```
'core': 'docroot/',
'module': 'docroot/sites/all/modules/contrib/{$name}/',
'theme': 'docroot/sites/all/themes/contrib/{$name}/',
'library': 'docroot/sites/all/libraries/{$name}/',
'profile': 'docroot/sites/all/profiles/contrib/{$name}/',
'drush': 'drush/{$name}/',
'custom-theme': 'docroot/sites/all/themes/custom/{$name}/',
'custom-module': 'docroot/sites/all/modules/custom/{$name}/',
```

## Contributing

Contributions are welcome. Please use the issue queue to describe the problem. Pull requests are welcome.

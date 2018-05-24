# WSUWP Platform

[![Build Status](https://travis-ci.org/washingtonstateuniversity/WSUWP-Platform.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/WSUWP-Platform)

A central publishing platform built on WordPress at Washington State University.

## Overview

## Local Development

This repository is configured to work alongside [Varying Vagrant Vagrants](https://varyingvagrantvagrants.org/) for local development. Please see [VVV's getting started guide](https://varyingvagrantvagrants.org/docs/en-US/installation/software-requirements/) for initial setup.

### VVV Configuration

Once VVV is installed, copy the `vvv-config.yml` file to `vvv-custom.yml` and add the following:

```
wsuwp:
  repo: https://github.com/washingtonstateuniversity/wsuwp-platform.git
  hosts:
    - wp.wsu.test
```

Once this is part of your `vvv-custom.yml` file, run `vagrant provision` or `vagrant provision --provision-with=site-wsuwp` to get started.

### Working with WSUWP inside VVV

The configuration above will put the WSUWP Platform project in a directory within your VVV structure: `{vvv-directory}/www/wsuwp/`. An initial set of MU plugins will be installed to provide multi-network functionality and a handful of the core decisions that we've made in building the WSUWP Platform.

Additional plugins should be installed manually.

#### Sync Production Files

The `bin/pull_plugins` script will retrieve all plugins in production and sync them with your local environment. To use these commands, access to the production server via a valid SSH key is required.

* Any local plugin directories that are git repositories will be ignored.
* Any plugin directories listed in `www/wp-conent/plugins/exclude.txt` will be ignored.

#### Site Specific Data

The nightly WP-CLI release should be installed on your local machine so that aliases for WSUWP and your local environment can be used.

1. Note the ID of the production site either through the dashboard or via a `wp` command.
  * `wp @wsuwp site list | grep web.wsu.edu`
1. Check the installed theme for the production site and ensure you have a theme in the exact same directory locally.
1. Create a new site in your local environment through the network dashboard. Note the ID of the site.
1. `bin/prep_local_db 7 9 web.wsu.edu dev.web.wsu.edu`
  * Exports a copy of the production tables for a site and imports them into the local environment.
  * Expects these arguments:
    * Remote site ID.
    * Local site ID.
    * Remote hostname.
    * Local hostname.
1. `bin/pull_site_uploads 7 9`
  * Sync uploads from production to the local environment, specifying the remote site ID first and the local site ID second.
1. Edit your hosts file or add a record to your *-wsuwp-hosts file and `vagrant up`.
1. Visit the dev site locally!

## Documentation

Additional documentation on specific pieces of the WSUWP Platform can be found in our `docs/` directory.

* [WSUWP Platform Structure](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/platform-structure.md)
* [Plugins and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/plugins.md)
* [Themes and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/themes.md)
* [Hooks added in WSUWP](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/hooks.md)

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
    - wsuwp.test
```

Once this is part of your `vvv-custom.yml` file, run `vagrant provision` or `vagrant provision --provision-with=site-wsuwp` to get started.

See VVV's [adding a new site documentation](https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/) for more information on this process.

### Working with WSUWP inside VVV

The configuration above will put the WSUWP Platform project in a directory within your VVV structure: `{vvv-directory}/www/wsuwp/`. An initial set of MU plugins will be installed to provide multi-network functionality and a handful of the core decisions that we've made in building the WSUWP Platform.

Additional plugins should be installed manually.

#### Sync Production Files

The `bin/pull_plugins` script will retrieve all plugins in production and sync them with your local environment. To use these commands, access to the production server via a valid SSH key is required.

* Any local plugin directories that are git repositories will be ignored.
* Any plugin directories listed in `www/wp-conent/plugins/exclude.txt` will be ignored.

## Documentation

Additional documentation on specific pieces of the WSUWP Platform can be found in our `docs/` directory.

* [WSUWP Platform Structure](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/platform-structure.md)
* [Plugins and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/plugins.md)
* [Themes and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/themes.md)
* [Hooks added in WSUWP](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/hooks.md)

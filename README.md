# WSUWP Platform

[![Build Status](https://travis-ci.org/washingtonstateuniversity/WSUWP-Platform.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/WSUWP-Platform)

A central publishing platform built on WordPress at Washington State University.

## Overview

## Local Development

A `Vagrantfile` is provided with this repository to allow for a provisioned development environment using [Vagrant](http://vagrantup.com).

The server configuration provisioned on the virtual machine is provided by the [WSU Web Provisioner](https://github.com/washingtonstateuniversity/wsu-web-provisioner) project. This server configuration is a close or exact match to what runs in production at Washington State University.

### Basic Requirements

1. Install [VirtualBox](http://virtualbox.org)
1. Install [Vagrant](http://vagrantup.com)
1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin
    * `vagrant plugin install vagrant-hostsupdater`
1. Install the [vagrant-hosts](https://github.com/adrienthebo/vagrant-hosts) plugin
    * `vagrant plugin install vagrant-hosts`

### Starting the Environment

#### Git

1. Clone this repository into a local directory. (`git clone ...`)
1. Navigate to the local directory in a terminal.
1. Type `vagrant up`

#### Zip

1. Download the zip file for the [latest stable release](https://github.com/washingtonstateuniversity/WSUWP-Platform/releases) and extract to a local directory.
1. Navigate to the local directory in a terminal.
1. Type `vagrant up`.

### Ongoing Environment Use

1. Use `vagrant suspend` to save the current state of the virtual machine to be brought back with `vagrant resume`.
1. Use `vagrant halt` to power off the virtual machine. Data will persist through another `vagrant up` to turn it back on.
1. Only use `vagrant destroy` to destroy the virtual machine and start from scratch.
1. `vagrant provision` will process provisioning again and ensure the proper services are started.
1. `vagrant reload` will act as a system reboot for the virtual machine. Data will persist.

### Sync Production

The `bin/pull_plugins` and `bin/pull_mu_plugins` scripts will retrieve all plugins in production and sync them with your local environment. To use these commands, access to the production server via a valid SSH key is required.

* Any local plugin directories that are git repositories will be ignored.
* Any plugin directories listed in `www/wp-conent/plugins/exclude.txt` or `www/wp-content/mu-plugins/exclude.txt` will be ignored.

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

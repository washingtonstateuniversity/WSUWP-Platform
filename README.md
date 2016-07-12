# WSUWP Platform

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

To use the following commands, access to the production server is required. The nightly WP-CLI release should be installed on your local machine so that aliases for WSUWP and your local environment can be used.

* `bin/pull_plugins` will retrieve all plugins in production and sync them with those that are local. Any local plugins with a `.git` file will be ignored. Any local plugins listed in `www/wp-content/plugins/exclude.txt` will be ignored.

#### Site Specific Data

1. Note the ID of the production site either through the dashboard or via a `wp` command.
  * `wp @wsuwp site list | grep web.wsu.edu`
1. Create a new site in your local environment through the network dashboard. Note the ID of the site.
1. Export a copy of the database tables for a specific site from production and then copy it to your local environment:
  * `wp @wsuwp db export web-wsu.sql --tables=$(wp @wsuwp db tables --url=web.wsu.edu --scope=blog --format=csv)`
  * `scp wsuwp-prod-01:web-wsu.sql ./www/`
1. Use the `prep_local_db` command to replace prefixed table names from production with that of the local site and then import the SQL into the local database.
  * `bin/prep_local_db 7 9 web-wsu.sql`
1. Search and replace the production URL with the local URL, uses of HTTPS for that URL, and the site ID in the uploads directory.
  * `wp @local search-replace "web.wsu.edu" "dev.web.wsu.edu" --url=dev.web.wsu.edu`
  * `wp @local search-replace "https://dev.web.wsu.edu" "http://dev.web.wsu.edu" --url=dev.web.wsu.edu`
  * `wp @local search-replace "sites/7/" "sites/9/" --url=dev.web.wsu.edu`
  * `wp @local cache flush`
1. Sync uploads from production to the local environment, specifying the remote site ID first and the local site ID second.
  * `bin/pull_site_uploads 7 9`

## Documentation

Additional documentation on specific pieces of the WSUWP Platform can be found in our `docs/` directory.

* [WSUWP Platform Structure](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/platform-structure.md)
* [Plugins and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/plugins.md)
* [Themes and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/themes.md)
* [Hooks added in WSUWP](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/hooks.md)

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

### Sync Production Files

**Note:** To use the following commands, access to the production server is required.

* `bin/pull_plugins` will retrieve all plugins in production and sync them with those that are local. Any local plugins with a `.git` file will be ignored. Any local plugins listed in `www/wp-content/plugins/exclude.txt` will be ignored.
* `bin/pull_site_uploads ID_1 ID_2` will retrieve all uploads for a site in production. `ID_1` should be the ID of the site in production. `ID_2` should be the ID of the site locally.

## Documentation

Additional documentation on specific pieces of the WSUWP Platform can be found in our `docs/` directory.

* [WSUWP Platform Structure](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/platform-structure.md)
* [Plugins and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/plugins.md)
* [Themes and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/themes.md)
* [Hooks added in WSUWP](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/hooks.md)

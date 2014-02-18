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
1. Initialize submodules with `git submodule init`
1. Initiate first sync of submodules with `git submodule update`
	* This will checkout the version of WordPress we're using for the platform.
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

## Documentation

Additional documentation on specific pieces of the WSUWP Platform can be found in our `docs/` directory.

* [WSUWP Platform Structure](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/platform-structure.md)
* [Plugins and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/plugins.md)
* [Themes and the WSUWP Platform](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/themes.md)
* [Hooks added in WSUWP](https://github.com/washingtonstateuniversity/WSUWP-Platform/blob/master/docs/hooks.md)
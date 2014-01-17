# WSUWP Platform: WordPress @ Washington State University

## Overview

## Local Development

A `Vagrantfile` is provided with this repository to allow for a provisioned development environment using [Vagrant](http://vagrantup.com).

The server configuration provisioned on the virtual machine is provided by the [WSU Web Serverbase](https://github.com/washingtonstateuniversity/wsu-web-serverbase) project. This server configuration is a close or exact match to what runs in production at Washington State University.

### Basic Requirements

1. Install [VirtualBox](http://virtualbox.org)
1. Install [Vagrant](http://vagrantup.com)
1. Install the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater) plugin
    * `vagrant plugin install vagrant-hostsupdater`
1. Install the [vagrant-hosts](https://github.com/adrienthebo/vagrant-hosts) plugin
    * `vagrant plugin install vagrant-hosts`

### Starting the Environment

1. Navigate to the directory where this repository was cloned or downloaded.
1. Type `vagrant up`

## Ignore all this for now...

### WordPress filters

* wsu_my_network_title
	* Used in WordPress admin bar to display 'My WSU Networks' by default.

### WordPress Admin Bar Structure

* WP Logo
	* About WSU WP
* WSU Networks
	* Network 1
	* Network 2
		* (Network Admin)
			* Dashboard
			* Theme
			* Plugins
			* Users
			* Sites
		* Site 1
		* Site 2
	* Network 3
* Current Site
	* Dashboard
	* Themes
	* etc...
* Comment Logo
* New
	* Post
	* Page
* -----
* Howdy, User []
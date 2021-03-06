# Plugins and the WSUWP Platform

Plugins play a major role in the WSUWP project. Through drop-ins, `mu-plugins`, and standard plugins, we're able to extend the default functionality of WordPress to fit the needs of a large, multi-network publishing platform.

The following will introduce the plugins that are in use throughout the system as well as the role each level plays. Some plugins will be managed completely at a global level, while others can be added for individual sites to add specific functionality when needed.

## Current Plugins

Plugins and their features that are currently provided as part of the WSUWP Platform in production.

### Must Use Plugins (wp-content/mu-plugins/)

Must use plugins, installed in `wp-content/mu-plugins/`, are included globally by default and contain some of the required functionality for the WSUWP Platform. These plugins cannot be disabled on a site or network level.

* WSU Roles and Capabilities
    * Implements the roles and capabilities required by WSU.
* WSUWP New Site Administration
    * Replaces the standard WordPress `site-new.php`
    * As temporary a solution as possible.
* WSU Network Admin
    * Modifications to handle multiple networks in WordPress.
* WSU Fight Song
    * A fork of [Hello Dolly](http://wordpress.org/plugins/hello-dolly/) containing the WSU Fight Song lyrics.
* WSU Remove Dashboard Widgets
    * Removes parts of the WordPress dashboard that WSU does not need.
* WSU Core Functions
    * Functions that perform some core functionality that we would love to live inside of WordPress one day.
* WSU Admin Bar
    * Modifies the WordPress admin bar.
* Batcache Manager
    * An optional plugin that ships with and improves [Batcache](http://wordpress.org/extend/plugins/batcache/), one of our drop-ins.
* WSU Co Authors Plus Skin
    * Loads a modified CSS file to better display Co-Authors Plus.

#### Drop-Ins (wp-content/)

Drop-Ins (or DropIns) allow for the replacement of certain WordPress functionality by *dropping* a specific file into the `wp-content/` directory.

* `advanced-cache.php`
    * Our current use case is primarily for [Batcache](http://wordpress.org/plugins/batcache), which adds an additional page caching layer to the object caching provided in our `object-cache.php` drop-in.
* `install.php`
    * Our current implementation is more of a test than anything. The intent is to provide replacement functions for various install and upgrade features. This may not be necessary for production.
* `object-cache.php`
    * Our current object cache drop in is [WordPress Memcached Backend](https://github.com/tollmanz/wordpress-memcached-backend), which allows us to use the PECL memcached backend rather than PECL memcache.
* `sunrise.php`
    * Allows for the routing of requested domains and URLs to the proper network and site. This is currently all custom functionality developed at WSU.

### Available Global Plugins (wp-content/plugins/)

Available plugins, installed in `wp-content/plugins/`, are not included globally, but are available on both network and site levels for activation.

### Development Plugins (wp-content/plugins/)

Plugins that are useful in a local development area, but that will not necessarily be available in production.

* [Query Monitor](https://github.com/johnbillion/query-monitor)
    * A WordPress plugin for monitoring database queries, hooks, conditionals, HTTP requests, query vars, environment, redirects, and more.
* [Rewrite Rules Inspector](https://github.com/Automattic/Rewrite-Rules-Inspector)
    * A straightforward WordPress admin tool for inspecting your rewrite rules.
* [User Switching](https://github.com/johnbillion/user-switching)
    * Allows you to quickly swap between user accounts in WordPress at the click of a button.
* [Debug Bar](http://wordpress.org/plugins/debug-bar/)
    * Adds a debug menu to the admin bar that shows query, cache, and other helpful debugging information.
* [Debug Bar Cron](https://github.com/tollmanz/debug-bar-cron)
    * Adds a new panel to Debug Bar that displays information about WP scheduled events.

## Plugins in Progress

Plugins and features that have been vetted and determined to be useful for the WSUWP Platform. Progress will need to be made toward certifying these plugins for production.

### WSU Plugins

### Open Source Plugins

* [Co-Authors Plus](https://github.com/Automattic/Co-Authors-Plus/)
    * Assign multiple bylines to posts, pages, and custom post types via a search-as-you-type input box.
* [The Events Calendar](http://wordpress.org/plugins/the-events-calendar/)
    * Create and manage events with ease.

## Plugins to Consider

Plugins and features that need to be vetted before including them in a process to deploy into production.

### Open Source Plugins

* [Edit Flow](https://github.com/Automattic/Edit-Flow)
    * Empowers you to collaborate with your editorial team inside WordPress.
* [MSM Sitemap](https://github.com/Automattic/msm-sitemap)
    * Comprehensive sitemaps for your WordPress site.
* [Jetpack](https://github.com/Automattic/jetpack)
    * Supercharges your self‑hosted WordPress site with the awesome cloud power of WordPress.com.
    * We'd likely want to avoid the 'cloud power' for most things. There should be an opportunity to use specific modules.

#### Development Plugins

* [Log Viewer](http://wordpress.org/plugins/log-viewer/)
    * Provides an easy way to view *.log files directly in the admin panel.
* [Monster Widget](http://wordpress.org/plugins/monster-widget/)
    * Provides a quick and easy method of adding all core widgets to a sidebar for testing purposes.
* [VIP Scanner](https://github.com/Automattic/vip-scanner)
    * Enables you to scan all sorts of themes and files and things.
* [Theme Check](http://wordpress.org/plugins/theme-check/)
    * A simple and easy way to test your theme for all the latest WordPress standards and practices.
* [Log Deprecated Notices](http://wordpress.org/plugins/log-deprecated-notices/)
    * Logs the usage of deprecated files, functions, and function arguments, and identifies where the deprecated functionality is being used.

### General Feature Requests

Features that may become plugins in progress.

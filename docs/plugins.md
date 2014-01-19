# Plugins and the WSUWP Platform

## Current Plugins

Plugins and their features that are currently provided as part of the WSUWP Platform in production.

### Must Use Plugins (mu-plugins)

Must use plugins, installed in `wp-content/mu-plugins/`, are included globally by default and contain some of the required functionality for the WSUWP Platform. These plugins cannot be disabled on a site or network level.

### Available Plugins (plugins)

Available plugins, installed in `wp-content/plugins/`, are not included globally, but are available on both network and site levels for activation.

### Development Plugins (plugins)

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

## Plugins to Consider

Plugins and features that need to be vetted before including them in a process to deploy into production.

### Open Source Plugins

#### Development Plugins (plugins)

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

# Changelog

## 1.5.2 (November 4, 2016)

* Allow uploads of VTT files.

## 1.5.1 (September 7, 2016)

* Update WordPress 4.6.1 (final)

## 1.5.0 (September 6, 2016)

* Remove unused `wp_get_networks()`.
* Add `edit_pages` and `upload_files` caps to author role.
* Add PHPCS configuration, connect with Travis.

## 1.4.19 (September 1, 2016)

* Update WordPress 4.6.1-RC1

## 1.4.18 (August 16, 2016)

* Update WordPress 4.6

## 1.4.17 (August 15, 2016)

* Update WordPress 4.6-RC4

## 1.4.16 (August 13, 2016)

* Update WordPress 4.6-RC3

## 1.4.15 (August 11, 2016)

* Update WordPress 4.6-RC2
* Add a script to sync production plugins locally.
* Add a script to sync production uploads locally.
* Add a script to sync production db tables locally.

## 1.4.14 (August 1, 2016)

### Enhancements

* Update WordPress 4.6-RC1
* Update internals to use new functions provided by WordPress 4.6.
* Remove old build directories for plugins/themes.

### Bugs

* Fix an issue where user searches outside of the main network would not work.

## 1.4.13 (June 21, 2016)

* Update WordPress 4.5.3
* Provide a local Nginx configuration for WSU Spine development.

## 1.4.12 (May 19, 2016)

* Add `WP_Site_Query` work to help test for inclusion in WordPress core.

## 1.4.11 (May 6, 2016)

* Upgrade WordPress 4.5.2

## 1.4.10 (April 26, 2016)

### Enhancements

* Upgrade WordPress 4.5.1

## 1.4.9 (April 25, 2016)

* Add logging for invalid domains in sunrise.
* Strip port numbers from requests that include them.

## 1.4.8 (April 12, 2016)

### Enhancements

* Remove custom global DB upgrade process.

## 1.4.7 (April 12, 2016)

### Enhancements

* Upgrade WordPress 4.5
* Disable the default multisite database upgrade routine.

## 1.4.6 (April 7, 2016)

### Bugs

* Fix an issue where the admin menu would be collapsed when viewing a submenu page from that section. This has been around since the beginning! Props @philcable for the spot.

## 1.4.5 (March 22, 2016)

### Enhancements

* Add caching to `wsuwp_get_networks()`.
* Add an `.editorconfig` file.

## 1.4.4 (March 7, 2016)

### Enhancements

* Add MatLab files as an allowed upload type.

## 1.4.3 (February 26, 2016)

### Enhancements

* Add `wsuwp_install_site_description` filter to allow the default site description to be filtered when a new site is created.
* Remove the use of the `spine_enable_builder_module` filter, which was specific to the Spine Parent Theme and is no longer needed.

## 1.4.2 (February 25, 2016)

### Enhancements

* Allow users to upload `.txt` files with a mime type of `text/plain`.

## 1.4.1 (February 3, 2016)

### Enhancements

* Apply patch for WordPress core ticket [#34395](https://core.trac.wordpress.org/ticket/34359) to cache the output of `wp_upload_dir()`.

### Bugs

* Allow for an existing global user to be added as a new network user in that network's admin.

## 1.4.0 (February 2, 2016)

### Enhancements

* Upgrade WordPress 4.4.2
* Allow network admins to promote users of their network to network admins.
* Search site URL as well as site name in each network's quick search under My Networks.
* Enable `zip` file uploads for all sites. "Extended" networks are no longer used.

### Bugs

* Clear the `wsuwp:site` cache key for a domain/path when a site is deleted.
* Ensure the "View" link is consistently places in the admin header when editing a post/page.
* Fix logic to determine if My Networks menu should be shown. Users of multiple networks will now see My Networks.
* Fix mistyped branch used for provisioning in `Vagrantfile`.

## 1.3.23 (January 15, 2016)

### Bugs

* Fix an issue where only a maximum of 100 sites would be used to determine what sites a user belongs to.

## 1.3.22 (January 8, 2016)

### Enhancements

* Upgrade WordPress 4.4.1

## 1.3.21 (December 15, 2015)

### Enhancements

* Remove some cruft from Batcache.

## 1.3.20 (December 10, 2015)

### Enhancements

* Upgrade WordPress 4.4.0

## 1.3.19 (November 19, 2015)

### Bugs

* Use the provided post object when clearing a trashed page's cache.

## 1.3.18 (November 16, 2015)

### Enhancements

* Port page and media taxonomy registration to WSUWP Admin plugin
* Remove unused `wsuwp_switch_to_site`, `wsuwp_restore_current_site`
* Remove `wsuwp_get_primary_network_id()`, use `get_main_network_id()` instead.

## 1.3.17 (November 10, 2015)

### Enhancements

* Provide `SERVER_PROTOCOL` specific versions of page cache.

## 1.3.16 (November 5, 2015)

### Enhancements

* Clean up old build process (Grunt) now that a different deploy strategy is in place. This was too opinionated previously.

### Bugs

* Remove an area of Advanced Batcache that uses `no_remote_groups`, which is not part of our object cache object.

## 1.3.15 (November 5, 2015)

### Bugs

* Re-fix an issue with WP Document Revisions and Batcache.

## 1.3.14 (October 27, 2015)

### Enhancements

* Update Batcache from upstream, introduces `$use_stale` configuration.

### Bugs

* Protect against the possibility of `get_current_screent()` not being available on a specific admin view.

## 1.3.13 (October 11, 2015)

### Enhancements

* Preview of upstream WP #20104 adjustment to network plugin views at the site admin level.

## 1.3.12 (October 2, 2015)

### Bugs

* Resolve rewrite rules with hotfix via https://core.trac.wordpress.org/changeset/34672

## 1.3.11 (September 15, 2015)

### Enhancements

* WordPress 4.3.1

## 1.3.10 (September 9, 2015)

### Enhancements

* Increase page cache max age to 10 minutes.
* Update PECL memcached drop in

### Bugs

* Upstream hotfix: Fix a reversal in parameters in WordPress when batch splitting terms. See https://core.trac.wordpress.org/changeset/33647

## 1.3.9 (September 2, 2015)

### Enhancements

* Attempt to speed up admin bar generation for users who belong to many sites.
* Remove custom handling of user count in MS Sites list table now that WordPress core includes this.

### Bugs

* Remove duplicate row actions in MS Sites list table after changes in WordPress 4.3.

## 1.3.8 (August 21, 2015)

### Bugs

* Remove an unused filter for managing site table action links.

## 1.3.5 (July 23, 2015)

### Enhancements

* WordPress 4.2.3
* Support *.wsu.edu addresses locally in addition to *.wsu.dev. This should be transitioned out to WSU Web Provisioner in a future release.
* Add basic munin support locally. This is not fully developed.

### Bugs

* Process user capabilities properly when moving a site between networks.

## 1.3.4 (May 21, 2015)

### Enhancements

* Provide a `wsuwp_is_global_admin()` to determine if a given user is a global (platform wide) admin.

## 1.3.3 (May 11, 2015)

### Enhancements

* WordPress 4.2.2

## 1.3.2 (April 28, 2015)

### Enhancements

* Add mu-plugin, BP Multi Network, to allow for separate BuddyPress instances on individual networks.

### Bugs

* Add `wsuwp:site` to the global cache group so that it is properly cleared when new sites are created.

## 1.3.1 (April 27, 2015)

### Enhancements

* WordPress 4.2.1

## 1.3.0 (April 23, 2015)

### Features
* WordPress 4.2

### Enhancements
* Remove files for WSU SSL functionality, defer to new WSU TLS plugin.
* Remove New Post and Manage Comments from admin menu if super admin to reduce HTML size.

## 1.2.0 (February 18, 2015)

### Enhancements
* Cleanup of styles around new site creation to make it more friendly.
* Cleanup of memcached dashboard widget number formatting.
* Set default timezone to America/Los Angeles on new sites.
* New My Networks screen to provide an additional interface to all networks.
* Add `.eps` as an allowed file upload type.
* VM specific php-fpm configuration for provisioning.
* Bump default upload size limit to 200MB.
* Update to WordPress 4.1, then 4.1.1.

### Bugs
* Resolve PHP errors where a screen type was being checked before we knew a screen was there.
* Clear domain/path request cache on new site creation.
* Allow the extended network option to be set when editing a network.
* Display the correct value for extended network in some cases.
* Avoid using Batcache for WP Document Revisions

## 1.1.0 (November 20, 2014)

### Features
* Provide method for updating DB schema on all sites from global admin.
* Add new `edit_javascript` capability and user meta for approving users.
* Add @trepmal plugin to search long site lists
* Add a global dashboard for memcached stats
* WordPress 4.0.1

### Enhancements
* Individual plugins and themes can now be part of the build process.
* Force permalink structure to avoid strange site issues, to revisit.
* Allow contributors to upload files and edit own pages
* Allow 3 character usernames
* Set file upload max to 150MB
* Add oga, ogv, ogg, webm, webp to allowed filetypes
* Clean up site menus for admins
* Hide batcache debug info by default
* Introduce `wsuwp_first_page_template` filter to replace default page template.
* Network admins can promote users on their networks.

### Bugs
* `current_site->blog_id` should always have the network's main `$blog_id` not the site.
* New networks now inherit globally active plugins.
* Globally active plugins can now be deactivated.
* Site admins can now activate and deactivate plugins.

## 1.0.0 (June 30, 2014)

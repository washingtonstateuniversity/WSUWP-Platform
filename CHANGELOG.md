# Changelog

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

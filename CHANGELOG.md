# Changelog

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

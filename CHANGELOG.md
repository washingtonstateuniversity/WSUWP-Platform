# Changelog

## 1.2.0 (TBD)

### Enhancements
* Cleanup of styles around new site creation to make it more friendly.
* Cleanup of memcached dashboard widget number formatting.
* Set default timezone to America/Los Angeles on new sites.

### Bugs
* Resolve PHP errors where a screen type was being checked before we knew a screen was there.
* Clear domain/path request cache on new site creation.

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

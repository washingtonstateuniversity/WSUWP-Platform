# Changelog

## 1.x.x (Unreleased)

### Features
* Provide method for updating DB schema on all sites from global admin.
* Add new `edit_javascript` capability and user meta for approving users.
* Add @trepmal plugin to search long site lists
* Add a global dashboard for memcached stats
* WordPress 4.0-beta4

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

### Bugs
* `current_site->blog_id` should always have the network's main `$blog_id` not the site.
* New networks now inherit globally active plugins.
* Globally active plugins can now be deactivated.

## 1.0.0 (June 30, 2014)

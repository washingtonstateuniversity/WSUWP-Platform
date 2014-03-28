# WSU SSO Authentication

Manages authentication for Washington State University WordPress installations.

* Current Stable: [v1.6](https://github.com/washingtonstateuniversity/WSUWP-Plugin-SSO-Authentication/releases/tag/v1.6)

## Overview

WSU SSO Authentication provides for authentication by WSU Network ID and through the standard WordPress login form.

* Users with roles other than **administrator** must use a password deemed strong by the [zxcvbn](https://github.com/lowe/zxcvbn) library included in WordPress core.
* Administrators may **not** authenticate with a password through WordPress and must use the WSU Network ID option instead.
* The promotion of a user to an administrator role will invalidate their current password with future authentication to be handled by WSU Network ID.

## Filters

The following filters are available to modify some default behavior provided by the plugin.

* `wsuwp_sso_create_new_user`
    * **Default:** If this filter returns `false`, users who authenticate using a WSU Network ID must already be added as a user in WordPress.
    * If this filter is set to return `true`, users who authenticate using a WSU Network ID will have a corresponding user created in WordPress automatically if it does not already exist.
* `wsuwp_sso_new_user_role`
    * **Default:** If a new user is created through a WSU Network ID login, assign the role of `subscriber` to the user.
    * Any WordPress role other than `administrator` can be assigned automatically to new WSU Network ID users by changing this filter.
* `wsuwp_sso_allow_wp_auth`
    * **Default:** False. Do not show an option to authenticate with WordPress.
    * If set to true, authentication via WordPress will be allowed. It is highly recommended that SSL be enabled on any server handling credentials for authentication.
* `wsuwp_sso_ad_auth_roles`
	* **Default:** Administrator.
	* If an array of roles is provided, these will be enforced as secure roles that can only be authenticated via WSU Network ID.

## Actions

The following actions are available to provide additional functionality as things happen in the plugin.

* `wsuwp_sso_user_created`
	* Fires after a new user is automatically created via SSO authentication.
	* Passes `$new_user_id` so that custom functionality can act accordingly.

## Public Functions

The following functions are available by default with the plugin.

* `wsuwp_is_user_logged_in()`
    * Mirrors some of the behavior of `is_user_logged_in()` in WordPress core.
    * Returns `true` or `false` depending on a user's current authentication status with their WSU Network ID.
* `wsuwp_get_current_user()`
    * Mirrors some of the behavior of `wp_get_current_user()` in WordPress core.
    * Returns `false` if the user is not authenticated with a WSU Network ID. Returns a `WP_User` object otherwise.
    * In the future, this will return additional data about the user.

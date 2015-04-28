<?php
/*
Plugin Name: BP Multi Network
Plugin URI: http://wpmututorials.com/news/new-features/multiple-buddypress-social-networks/
Description: Segregate your BP networks in a multi-network WP install (must be installed in the mu-plugins folder)
Version: 0.1.1
Author: Ron Rennick
Author URI: http://ronandandrea.com/
*/
/* Copyright:	(C) 2011 Ron Rennick, All rights reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function ra_bp_multinetwork_filter( $prefix ) {
	global $wpdb;

	if( $wpdb->siteid > 1 && $prefix == $wpdb->base_prefix ) {
		$current_site = get_current_site();
		return $wpdb->get_blog_prefix( $current_site->blog_id );
	}

	return $prefix;
}
add_filter( 'bp_core_get_table_prefix', 'ra_bp_multinetwork_filter' );

function ra_bp_multinetwork_meta_key_filter( $key ) {
	global $wpdb;
	static $user_meta_keys = array(
		'last_activity' => false,
		'bp_new_mention_count' => false,
		'bp_favorite_activities' => false,
		'bp_latest_update' => false,
		'total_friend_count' => false,
		'total_group_count' => false,
		'notification_groups_group_updated' => false,
		'notification_groups_membership_request' => false,
		'notification_membership_request_completed' => false,
		'notification_groups_admin_promotion' => false,
		'notification_groups_invite' => false,
		'notification_messages_new_message' => false,
		'notification_messages_new_notice' => false,
		'closed_notices' => false,
		'profile_last_updated' => false,
		'notification_activity_new_mention' => false,
		'notification_activity_new_reply' => false
	);

	if( $wpdb->siteid < 2 || !isset( $user_meta_keys[$key] ) )
		return $key;

	if( !$user_meta_keys[$key] ) {
		$current_site = get_current_site();
		$user_meta_keys[$key] = $wpdb->get_blog_prefix( $current_site->blog_id ) . $key;
	}

	return $user_meta_keys[$key];
}
add_filter( 'bp_get_user_meta_key', 'ra_bp_multinetwork_meta_key_filter' );
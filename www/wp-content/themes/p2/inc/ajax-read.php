<?php
/**
 * Handle Ajax read requests.
 *
 * @package P2
 */

class P2Ajax_Read {
	function dispatch() {
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

		do_action( "p2_ajax", $action );
		if ( is_callable( array( 'P2Ajax_Read', $action ) ) ) {
			status_header( 200 );
			call_user_func( array( 'P2Ajax_Read', $action ) );
		} else {
			die( '-1' );
		}
		exit;
	}

	/*
	 * Is the viewer logged in or not?
	 */
	function logged_in_out() {
		check_ajax_referer( 'ajaxnonce', '_loggedin' );
		echo is_user_logged_in() ? 'logged_in' : 'not_logged_in';
	}

	function tag_search() {
		global $wpdb;
		$term = $_GET['term'];
		if ( false !== strpos( $term, ',' ) ) {
			$term = explode( ',', $term );
			$term = $term[count( $term ) - 1];
		}
		$term = trim( $term );
		if ( strlen( $term ) < 2 )
			die(); // require 2 chars for matching

		$tags = array();
		$results = $wpdb->get_results( "SELECT name, count FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = 'post_tag' AND t.name LIKE ( '%". like_escape( $wpdb->escape( $term ) ) . "%' ) ORDER BY count DESC" );

		foreach ( $results as $result ) {
			$rterm = '/' . preg_quote( $term, '/' ) . '/i';
			$label = preg_replace( $rterm, "<strong>$0</strong>", $result->name ) . " ($result->count)";

			$tags[] = array(
				'label' => $label,
				'value' => $result->name,
			);
		}

		echo json_encode( $tags );
	}

	function get_latest_posts() {
		global $post_request_ajax;

		$load_time = $_GET['load_time'];
		$frontpage = $_GET['frontpage'];
		$num_posts = 10; // max amount of posts to load
		$number_of_new_posts = 0;
		$visible_posts = isset( $_GET['vp'] ) ? (array)$_GET['vp'] : array();

		query_posts( 'showposts=' . $num_posts . '&post_status=publish' );
		ob_start();
		while ( have_posts() ) : the_post();
			$current_user_id = get_the_author_meta( 'ID' );

			// Avoid showing the same post if it's already on the page
			if ( in_array( get_the_ID(), $visible_posts ) )
				continue;

			// Only show posts with post dates newer than current timestamp
			if ( get_gmt_from_date( get_the_time( 'Y-m-d H:i:s' ) ) <= $load_time )
				continue;

			$number_of_new_posts++;
			$post_request_ajax = true;

			p2_load_entry( false );
		endwhile;
		$posts_html = ob_get_clean();

		if ( $number_of_new_posts != 0 ) {
			nocache_headers();
			echo json_encode( array(
				'numberofnewposts' => $number_of_new_posts,
				'html' => $posts_html,
				'lastposttime' => gmdate( 'Y-m-d H:i:s' )
			) );
		} else {
			nocache_headers();
			header( 'HTTP/1.1 200 OK' );
			exit;
		}
	}

	function get_latest_comments() {
		global $wpdb, $comments, $comment, $max_depth, $depth, $user_login, $user_ID, $user_identity;

		$number = 10; //max amount of comments to load
		$load_time = $_GET['load_time'];
		$lc_widget = $_GET['lcwidget'];
		$visible_posts = isset($_GET['vp'])? (array)$_GET['vp'] : array();

		if ( get_option( 'thread_comments' ) )
			$max_depth = get_option( 'thread_comments_depth' );
		else
			$max_depth = -1;

		// Since we currently cater the same HTML to all widgets,
		// the instances without avatars will have to remove the avatar in javascript
		$avatar_size = 32;

		// Check for non-logged-in users and fetch their comment author information from comment cookies
		if ( empty( $user_ID ) && empty( $comment_author ) ) {
			$commenter = wp_get_current_commenter();

			// The name of the current comment author escaped for use in attributes
			$comment_author = $commenter['comment_author']; // Escaped by sanitize_comment_cookies()

	 		// The email address of the current comment author escaped for use in attributes
			$comment_author_email = $commenter['comment_author_email']; // Escaped by sanitize_comment_cookies()
		}

		// Get new comments
		if ( $user_ID ) {
			$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) ) AND comment_date_gmt > %s ORDER BY comment_date_gmt DESC LIMIT $number", $user_ID, $load_time ) );
		} elseif ( ! empty( $comment_author ) ) {
			$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE (comment_approved = '1' OR ( comment_author = %s AND comment_author_email = %s AND comment_approved = '0' ) ) AND comment_date_gmt > %s ORDER BY comment_date_gmt DESC LIMIT $number", $comment_author, $comment_author_email, $load_time ) );
		} else {
			$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_approved = '1' AND comment_date_gmt > %s ORDER BY comment_date_gmt DESC LIMIT $number", $load_time ) );
		}
		$number_of_new_comments = count( $comments );

		$prepare_comments = array();
		if ($number_of_new_comments > 0) {
			foreach ($comments as $comment) {
				// Setup comment html if post is visible
				$comment_html = '';
				if ( in_array( $comment->comment_post_ID, $visible_posts ) ) {
					ob_start();
					p2_comments($comment, array( 'max_depth' => $max_depth, 'before' => ' | ' ), $depth );
					$comment_html = ob_get_clean();
				}

				// Setup widget html if widget is visible
				$comment_widget_html = '';
				if ( $lc_widget )
					$comment_widget_html = P2_Recent_Comments::single_comment_html( $comment, $avatar_size );

				$prepare_comments[] = array( "id" => $comment->comment_ID, "postID" => $comment->comment_post_ID, "commentParent" => $comment->comment_parent,
					"html" => $comment_html, "widgetHtml" => $comment_widget_html );
			}
			$json_data = array("numberofnewcomments" => $number_of_new_comments, "comments" => $prepare_comments, "lastcommenttime" => gmdate( 'Y-m-d H:i:s' ) );

			echo json_encode( $json_data );
		} else { // No new comments
			nocache_headers();
			header( 'HTTP/1.1 200 OK' );
			exit;
		}
	}

	/*
	 * Create a comment.
	 * The Exception: the comment creation endpoint is public (it's not in /wp-admin/).
	 */
	function new_comment() {
		if ( empty( $_POST['action'] ) || $_POST['action'] != 'new_comment' )
		    die();

		check_ajax_referer( 'ajaxnonce', '_ajax_post' );

		$comment_content = isset( $_POST['comment'] ) ? trim( $_POST['comment'] ) : null;
		$comment_post_ID = isset( $_POST['comment_post_ID'] ) ? trim( $_POST['comment_post_ID'] ) : null;

		$user = wp_get_current_user();

		if ( is_user_logged_in() ) {
			if ( empty( $user->display_name ) )
				$user->display_name = $user->user_login;
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
			$comment_author_url   = $user->user_url;
			$user_ID 			  = $user->ID;
		} else {
			if ( get_option( 'comment_registration' ) ) {
			    die( '<p>'.__( 'Error: you must be logged in to post a comment.', 'p2' ).'</p>' );
			}
			$comment_author       = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
			$comment_author_email = ( isset($_POST['email']) )   ? trim($_POST['email']) : null;
			$comment_author_url   = ( isset($_POST['url']) )     ? trim($_POST['url']) : null;
		}

		$comment_type = '';

		if ( get_option( 'require_name_email' ) && !$user->ID )
			if ( strlen( $comment_author_email ) < 6 || '' == $comment_author ) {
				die( '<p>'.__( 'Error: please fill the required fields (name, email).', 'p2' ).'</p>' );
			} elseif ( !is_email( $comment_author_email ) ) {
			    die( '<p>'.__( 'Error: please enter a valid email address.', 'p2' ).'</p>' );
			}

		if ( '' == $comment_content )
		    die( '<p>'.__( 'Error: Please type a comment.', 'p2' ).'</p>' );

		$comment_parent = isset( $_POST['comment_parent'] ) ? absint( $_POST['comment_parent'] ) : 0;

		$commentdata = compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID' );

		$comment_id = wp_new_comment( $commentdata );
		$comment = get_comment( $comment_id );
		if ( !$user->ID ) {
			setcookie( 'comment_author_' . COOKIEHASH, $comment->comment_author, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			setcookie( 'comment_author_email_' . COOKIEHASH, $comment->comment_author_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			setcookie( 'comment_author_url_' . COOKIEHASH, esc_url($comment->comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
		}
		if ($comment) echo $comment_id;
		else echo __("Error: Unknown error occurred. Comment not posted.", 'p2' );
	}
}

<?php

function spine_section_meta( $attribute = 'slug', $sectional = 'subsection' ) {
	if ( empty( $sectional ) ) {
		$sectional = 'subsection';
	}

	if ( empty( $attribute ) || 'slug' == $attribute ) {
		$attribute = 'post_name';
	}

	if ( 'title' == $attribute ) {
		$attribute = 'post_title';
	}

	$subsections = get_post_ancestors( get_the_ID() );
	if ( ! empty( $subsections ) ) {
		$subsection = get_post( $subsections[0] );
		$sections = array_reverse( $subsections );
		$section = get_post( $sections[0] );

		if ( isset( $sectional ) && in_array( $sectional, array( 'section', 'top' ) ) ) {
			return $section->$attribute;
		} else {
			return $subsection->$attribute;
		}
	}

	return null;
}

function spine_get_main_header() {
	$page_for_posts = absint( get_option( 'page_for_posts', 0 ) );

	if ( 0 !== $page_for_posts ) {
		$posts_page_title = get_the_title( $page_for_posts );
	} else {
		$posts_page_title = '';
	}

	$site_name          = get_bloginfo( 'name', 'display' );
	$site_tagline       = get_bloginfo( 'description', 'display' );
	$page_title         = get_the_title();
	$post_title         = get_the_title();
	$section_title      = spine_section_meta( 'title', 'section' );
	$subsection_title   = spine_section_meta( 'title', 'subsection' );

	$sup_header_default	  = '<a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . $site_name . '</a>';
	$sub_header_default   = spine_section_meta( 'title', 'subsection' );
	$sup_header_alternate = '';
	$sub_header_alternate = '';

	// On a category archive view, use the page title of the page_for_posts setting as the sup
	// header if available, otherwise fallback to the site name.
	if ( is_category() ) {
		if ( 0 === $page_for_posts ) {
			$sup_header_default = '<a href="' . esc_url( home_url() ) . '">' . esc_html( $site_name ) . '</a>';
			$sub_header_default = single_cat_title( '', false );
			$section_title = $site_name;
		} else {
			$sup_link = get_permalink( $page_for_posts );
			$sup_header_default = '<a href="' . esc_url( $sup_link ) . '">' . $posts_page_title . '</a>';
			$sub_header_default = single_cat_title( '', false );
			$section_title = $posts_page_title;
		}
	}

	// On date archive views, use one of the day, month, year as the sub header. If a tag or other
	// non category archive, use 'Archives'. Use the page title of page_for_posts if available as the
	// sup header, otherwise use the site name.
	if ( is_archive() && ! is_category() ) {
		if ( is_day() ) {
			$sub_header_default = get_the_date();
		} else if ( is_month() ) {
			$sub_header_default = get_the_date( 'F Y' );
		} else if ( is_year() ) {
			$sub_header_default = get_the_date( 'Y' );
		} else if ( is_author() ) {
			$sub_header_default = get_the_author();
		} else {
			$sub_header_default = 'Archives';
		}

		if ( 0 === $page_for_posts ) {
			$sup_header_default = $site_name;
			$section_title = $site_name;
		} else {
			$sup_header_default = $posts_page_title;
			$section_title = $posts_page_title;
		}
	}

	// For any posts or post types, if page_for_posts is not set or this view is
	// of a custom post type, use the post type's label as the sub header. Otherwise
	// use the title of the page_for_posts page.
	if ( is_single() ) {
		if ( 0 === $page_for_posts || ! is_singular( 'post' ) ) {
			$post = get_post();
			$post_type = get_post_type_object( get_post_type( $post ) );
			$sub_header_default = $post_type->labels->name;
		} else {
			$sub_header_default = $posts_page_title;
		}
	}

	// If this page is a child of another page, use the subsection title as a sub
	// header. Otherwise, use the current page's title.
	if ( is_page() ) {
		if ( spine_is_sub() ) {
			$post = get_post();
			$sub_link = get_permalink( $post->post_parent );
			$sub_header_default = '<a href="' . $sub_link . '">' . $subsection_title . '</a>';
		} else {
			$sub_header_default = $page_title;
		}
	}

	if ( is_front_page() && is_page() ) {
		$sub_header_default = $site_tagline;
	}

	if ( is_home() && ! is_front_page() ) {
		$sup_header_default = $site_name;

		if ( 0 === $page_for_posts ) {
			$page_title = $site_name;
			$sub_header_default = $site_name;
		} else {
			$sub_header_default = $posts_page_title;
			$page_title = $posts_page_title;
		}
	}

	if ( is_search() ) {
		$sup_header_alternate = 'Search Terms';
		$sub_header_default = 'Search Results';
		$sub_header_alternate = esc_html( get_search_query() );
	}

	if ( is_404() ) {
		$sub_header_default = 'Page not found';
	}

	// Both sup and sub headers can be overridden with the use of post meta.
	if ( is_singular() ) {
		$sup_override = get_post_meta( get_the_ID(), 'sup-header', true );
		$sub_override = get_post_meta( get_the_ID(), 'sub-header', true );

		if ( ! empty( $sup_override ) ) {
			$sup_header_default = wp_kses_post( $sup_override );
		}
		if ( ! empty( $sub_override ) ) {
			$sub_header_default = wp_kses_post( $sub_override );
		}
	}

	$sup_header_default = apply_filters( 'spine_sup_header_default', $sup_header_default );
	$sub_header_default = apply_filters( 'spine_sub_header_default', $sub_header_default );

	$main_header_elements = array(
		'site_name'				=>	$site_name,
		'site_tagline'			=>	$site_tagline,
		'page_title'			=>	$page_title,
		'post_title'			=>	$post_title,
		'section_title'			=>	$section_title,
		'subsection_title'		=>	$subsection_title,
		'posts_page_title'		=>	$posts_page_title,
		'sup_header_default'	=>	$sup_header_default,
		'sub_header_default'	=>	$sub_header_default,
		'sup_header_alternate'	=>	$sup_header_alternate,
		'sub_header_alternate'	=>	$sub_header_alternate
	);

	return apply_filters( 'spine_main_header_elements', $main_header_elements );
}

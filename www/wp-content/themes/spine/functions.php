<?php

include_once( 'includes/main-header.php' ); // Include main header functionality.
include_once( 'includes/customizer.php' ); // Include customizer functionality.

// Two Navigation Menus
add_action( 'init', 'spine_theme_menus' );
function spine_theme_menus() {
	register_nav_menus(
		array(
		'site'    => 'Site',
		'offsite' => 'Offsite',
		)
	);
}

// A Single Sidebar
add_action( 'widgets_init', 'spine_theme_widgets_init' );
/**
 * Register sidebars used by the theme.
 */
function spine_theme_widgets_init() {
	$widget_options = array(
		'name'          => __( 'Sidebar', 'sidebar' ),
		'id'            => 'sidebar',
		'before_widget' => '<aside id="%1$s2" class="%2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<header>',
		'after_title'   => '</header>'
	);
	register_sidebar( $widget_options );
}

add_action( 'after_setup_theme', 'spine_theme_setup_theme' );
/**
 * Setup some defaults provided by the theme.
 */
function spine_theme_setup_theme() {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 198, 198, true );

	add_image_size( 'teaser-image', 198, 198, true );
	add_image_size( 'header-image', 792, 99163 );
	add_image_size( 'billboard-image', 1584, 99163 );
}

add_filter( 'nav_menu_css_class', 'spine_abbridged_menu_classes', 10 );
/**
 * Condense verbose menu classes provided by WordPress.
 *
 * Removes the default current-menu-item and current_page_parent classes
 * if they are found on this page view and replaces them with 'current'.
 *
 * @param array $classes Current list of nav menu classes.
 *
 * @return array Modified list of nav menu classes.
 */
function spine_abbridged_menu_classes( $classes ) {
	if ( in_array( 'current-menu-item', $classes ) || in_array( 'current_page_parent', $classes ) ) {
		return array( 'current' );
	}

	return array();
}

add_action( 'admin_init', 'spine_theme_image_options' );
function spine_theme_image_options() {
	// Default Image Sizes
	update_option( 'thumbnail_size_w', 198   );
	update_option( 'thumbnail_size_h', 198   );
	update_option( 'medium_size_w',    396   );
	update_option( 'medium_size_h',    99163 );
	update_option( 'large_size_w',     792   );
	update_option( 'large_size_h',     99163 );
	// update_option('full_size_w', 1980);
	// update_option('full_size_h', 99163);
}

/* Default Image Markup */

add_filter( 'img_caption_shortcode', 'spine_theme_caption_markup', 10, 3 );

function spine_theme_caption_markup( $output, $attr, $content ) {
	if ( is_feed() ) {
		return $output;
	}

	$defaults = array(
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => ''
	);

	$attr = shortcode_atts( $defaults, $attr );
	if ( 1 > $attr['width'] || empty( $attr['caption'] ) ) {
		return $content;
	}

	$attributes = ( !empty( $attr['id'] ) ? ' id="' . esc_attr( $attr['id'] ) . '"' : '' );
	$attributes .= ' class="' . esc_attr( $attr['align'] ) . '"';
	$output = '<figure' . $attributes .'><div class="liner cf">';
	$output .= do_shortcode( $content );
	$output .= '<figcaption>' . $attr['caption'] . '</figcaption>';
	$output .= '</div></figure>';

	return $output;
}

/* add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', 'remove_width_attribute', 10 );

function remove_width_attribute( $html ) {
   $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
   return $html;
} */

/* function image_tag_class($class, $id, $align, $size) {
	return $align;
}
add_filter('get_image_tag_class', 'image_tag_class', 0, 4);

*/

// SECTIONING

function spine_is_sub() {
    $post = get_post();

    if ( is_page() && $post->post_parent ) {
        return $post->post_parent;
    } else {
		return false;
	}
}

add_filter( 'body_class','spine_speckled_body_classes' );
/**
 * Add randomized body classes.
 *
 * @param array $classes Current list of body classes.
 *
 * @return array Modified list of body classes.
 */
function spine_speckled_body_classes( $classes ) {
	$classes[] = 'five' . mt_rand( 1, 5 );
	$classes[] = 'ten' . mt_rand( 1, 10 );
	$classes[] = 'twenty' . mt_rand( 1, 20 );

	return $classes;
}

add_filter('body_class', 'spine_categorized_body_classes');
/* Add categorized in classes to body on singular views */
function spine_categorized_body_classes( $classes ) {
	if ( has_category() && is_singular() ) {
		foreach( get_the_category( get_the_ID() ) as $category ) {
			$classes[] = 'categorized-' . $category->slug;
		}
	}

	return array_unique( $classes );
}

add_filter( 'body_class', 'spine_sectioned_body_classes' );
/**
 * Add custom body classes based on the requested URL for individual
 * page and post views.
 *
 * @param array $classes Current list of body classes.
 *
 * @return array Modified list of body classes.
 */
function spine_sectioned_body_classes( $classes ) {

	// Paths may be polluted with additional site information, so we
	// compare the post/page permalink with the home URL.
	$path = str_replace( get_home_url(), '', get_permalink() );
	$path = trim( $path, '/' );
	$path = explode( '/', $path );

	if ( is_singular() && ! empty( $path ) ) {
		$depth = count( $path ) - 1;
		$classes[] = 'depth-' . $depth;

		if ( 1 === count( $path ) ) {
			$classes[] = 'section-' . $path[0];
			$classes[] = 'page-' . $path[0];
		} else {
			$classes[] = 'section-' . array_shift( $path );
			$prefix = 'sub-';
			foreach( $path as $part ) {
				$classes[] = $prefix.'section-' . $part;
				$prefix = 'sub-'.$prefix;
			}
			$classes[] = 'page-' . array_pop( $path );
		}
	}

	return array_unique( $classes );
}

// Default Read More
function spine_theme_excerpt_more() {
	return ' <a class="read-more" href="'. get_permalink( get_the_ID() ) . '" >More</a>';
}
add_filter( 'excerpt_more', 'spine_theme_excerpt_more' );

// TEMPLATES

// ADMIN MODS

// Add CSS files
function spine_theme_admin_styles() {
	wp_enqueue_style( 'admin-interface-styles', get_template_directory_uri() . '/includes/admin.css' );
	add_editor_style( 'admin-editor-styles', get_template_directory_uri() . '/includes/editor.css' );
}
add_action( 'admin_enqueue_scripts', 'spine_theme_admin_styles' );

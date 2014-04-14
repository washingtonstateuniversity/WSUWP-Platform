<?php

// http://codex.wordpress.org/Plugin_API/Action_Reference/customize_register
// http://ottopress.com/2012/making-a-custom-control-for-the-theme-customizer/

function spine_theme_customize_styles() {
    wp_enqueue_style('customize-interface-styles', get_template_directory_uri() . '/includes/customize.css');
}
add_action( 'customize_controls_enqueue_scripts', 'spine_theme_customize_styles' );

function spine_theme_customize_scripts() {
    wp_enqueue_script('customize-interface-scripts', get_template_directory_uri().'/includes/customize.js', array( 'jquery' ),'',true );
}
add_action( 'customize_controls_enqueue_scripts', 'spine_theme_customize_scripts' );

function spine_customize_register($wp_customize){
 
    // Spine Options
    
    $wp_customize->add_section('section_spine_options', array(
        'title'    => __('Spine: Options', 'spine'),
        'priority' => 124,
    ));
 
    // Grid
    $wp_customize->add_setting('spine_options[grid_style]', array(
        'default'        => 'hybrid',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_grid_style', array(
        'label'      => __('Grid Behavior', 'spine'),
        'section'    => 'section_spine_options',
        'settings'   => 'spine_options[grid_style]',
        'type'       => 'radio',
        'choices'    => array(
            'fixed' => 'Fixed',
            'hybrid' => 'Hybrid',
            'fluid' => 'Fluid'
        ),
    ));
    
 
    // Spine Color
    $wp_customize->add_setting('spine_options[spine_color]', array(
        'default'        => 'white',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
 
    ));
    $wp_customize->add_control( 'spine_color_select', array(
        'settings' => 'spine_options[spine_color]',
        'label'   => 'Spinal Column Color',
        'section' => 'section_spine_options',
        'type'    => 'select',
        'choices'    => array(
            'white' => 'Default (white)',
            'lightest' => 'Lightest',
            'lighter' => 'Lighter',
            'light' => 'Light',
            'gray' => 'Gray',
            'dark' => 'Dark',
            'darker' => 'Darker',
            'darkest' => 'Darkest (black)',
            'crimson' => 'Crimson',
            'velum' => 'Transparent'
        ),
    ));
    
     
    
    
    // Bleed Spine Leftward
    $wp_customize->add_setting('spine_options[bleed]', array(
        'default'        => false,
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_bleed', array(
        'label'      => __('Bleed Spine Left', 'spine'),
        'section'    => 'section_spine_options',
        'settings'   => 'spine_options[bleed]',
        'type'       => 'checkbox'
    ));
    
    $wp_customize->add_section('static_front_page', array(
        'title'    => __('Front Page', 'static_front_page'),
    ));
    
    
    
    // SOCIAL CHANNELS
    $wp_customize->add_section('section_spine_social', array(
        'title'    => __('Spine: Social', 'spine'),
        'priority' => 300,
        'description'    => __( 'You can retain, replace, or remove social channels. Select "None" to remove/hide a location.' ),
    ));
    
    // Location One
    $wp_customize->add_setting('spine_options[social_spot_one]', array( 'default' => 'http://www.facebook.com', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_one', array( 'section' => 'section_spine_social', 'settings' => 'spine_options[social_spot_one]', 'priority' => 302 ));
    $wp_customize->add_setting('spine_options[social_spot_one_type]', array( 'default' => 'facebook', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_one_type', array(
    	'label' => __('Location One', 'spine'),
    	'section' => 'section_spine_social',
    	'settings' => 'spine_options[social_spot_one_type]',
    	'type' => 'select',
    	'choices' => array('none' => 'None', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'youtube' => 'YouTube', 'directory' => 'Directory', 'linkedin' => 'LinkedIn', 'tumblr' => 'Tumblr', 'pinterest' => 'Pinterest'),
    	'priority' => 301
    	));
    
    // Location Two
    $wp_customize->add_setting('spine_options[social_spot_two]', array( 'default' => 'http://twitter.com/wsupullman', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_two', array( 'section' => 'section_spine_social', 'settings' => 'spine_options[social_spot_two]', 'priority' => 304 ));
    
    $wp_customize->add_setting('spine_options[social_spot_two_type]', array( 'default' => 'twitter', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_two_type', array(
    	'label' => __('Location Two', 'spine'), 
    	'section' => 'section_spine_social',
    	'settings' => 'spine_options[social_spot_two_type]',
    	'type' => 'select',
    	'choices' => array('none' => 'None', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'youtube' => 'YouTube', 'directory' => 'Directory', 'linkedin' => 'LinkedIn', 'tumblr' => 'Tumblr', 'pinterest' => 'Pinterest'),
    	'priority' => 303
    	));
    
    // Location Three
    $wp_customize->add_setting('spine_options[social_spot_three]', array( 'default' => 'http://www.youtube.com/washingtonstateuniv', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_three', array( 'section' => 'section_spine_social', 'settings' => 'spine_options[social_spot_three]', 'priority' => 306 ));
    
    $wp_customize->add_setting('spine_options[social_spot_three_type]', array( 'default' => 'youtube', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_three_type', array(
    	'label' => __('Location Three', 'spine'),
    	'section' => 'section_spine_social',
    	'settings' => 'spine_options[social_spot_three_type]',
    	'type' => 'select',
    	'choices' => array('none' => 'None', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'youtube' => 'YouTube', 'directory' => 'Directory', 'linkedin' => 'LinkedIn', 'tumblr' => 'Tumblr', 'pinterest' => 'Pinterest'),
    	'priority' => 305
    	));
    
    // Location Four
    $wp_customize->add_setting('spine_options[social_spot_four]', array( 'default' => 'http://social.wsu.edu', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_four', array( 'section' => 'section_spine_social', 'settings' => 'spine_options[social_spot_four]', 'priority' => 308 ));
    
    $wp_customize->add_setting('spine_options[social_spot_four_type]', array( 'default' => 'directory', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('social_spot_four_type', array(
    	'label' => __('Location Four', 'spine'),
    	'section' => 'section_spine_social',
    	'settings' => 'spine_options[social_spot_four_type]',
    	'type' => 'select',
    	'choices' => array('none' => 'None', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'youtube' => 'YouTube', 'directory' => 'Directory', 'linkedin' => 'LinkedIn', 'tumblr' => 'Tumblr', 'pinterest' => 'Pinterest'),
    	'priority' => 307
    	));
    	
    	
    // Contact
    
    $wp_customize->add_section('section_spine_contact', array(
        'title'    => __('Spine: Contact', 'spine'),
        'priority' => 315,
        'description'    => __( 'This is the official contact for your website.' ),
    ));
    
    $wp_customize->add_setting('spine_options[contact_department]', array( 'default' => '', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_department', array( 'label' => 'Your Unit (Dep., College, etc.)', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_department]', 'priority' => 405 ));
    
    $wp_customize->add_setting('spine_options[contact_streetAddress]', array( 'default' => 'PO Box 99164', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_streetAddress', array( 'label' => 'Your Address', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_streetAddress]', 'type' => 'text', 'priority' => 410 ));
    $wp_customize->add_setting('spine_options[contact_addressLocality]', array( 'default' => 'Pullman, WA', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_addressLocality', array( 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_addressLocality]', 'type' => 'text', 'priority' => 411 ));
    
    $wp_customize->add_setting('spine_options[contact_postalCode]', array( 'default' => '99164', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_postalCode', array( 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_postalCode]', 'type' => 'text', 'priority' => 411 ));
    
    $wp_customize->add_setting('spine_options[contact_telephone]', array( 'default' => '509-335-3564', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_telephone', array( 'label' => 'Best Phone Number', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_telephone]', 'type' => 'text', 'priority' => 415 ));
    
    $wp_customize->add_setting('spine_options[contact_email]', array( 'default' => '', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_email', array( 'label' => 'Best Email Address', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_email]', 'type' => 'text', 'priority' => 420 ));
    
    $wp_customize->add_setting('spine_options[contact_ContactPoint]', array( 'default' => 'http://contact.wsu.edu', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_ContactPoint', array( 'label' => 'Contact Page/Directory (Optional)', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_ContactPoint]', 'type' => 'text', 'priority' => 425 ));
    $wp_customize->add_setting('spine_options[contact_ContactPointTitle]', array( 'default' => 'Contact Page...', 'capability' => 'edit_theme_options', 'type' => 'option' ));
    $wp_customize->add_control('contact_ContactPointTitle', array( 'label' => 'Contact Link Title', 'section' => 'section_spine_contact', 'settings' => 'spine_options[contact_ContactPointTitle]', 'type' => 'text', 'priority' => 426 ));


    // Advanced
    $wp_customize->add_section('section_spine_advanced_options', array(
        'title'    => __('Spine: Advanced', 'spine_advanced'),
        'priority' => 320,
    ));
    
    // Large Format
    $wp_customize->add_setting('spine_options[large_format]', array(
        'default'        => '',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_large_format', array(
        'label'      => __('Large Format', 'spine'),
        'section'    => 'section_spine_advanced_options',
        'settings'   => 'spine_options[large_format]',
        'type'       => 'select',
        'choices'    => array(
            ''  => 'Default Width of 990px',
            ' folio max-1188' => 'Max Width 1188px',
            ' folio max-1386' => 'Max Width 1386px',
            ' folio max-1584' => 'Max Width 1584px',
            ' folio max-1782' => 'Max Width 1782px',
            ' folio max-1980' => 'Max Width 1980px',
            ' folio max-full' => 'Max Width 100%',            
        ),
    ));
    
    // Bleed Main Rightward
    $wp_customize->add_setting('spine_options[broken_binding]', array(
        'default'        => false,
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_broken_binding', array(
        'label'      => __('Bleed Main Right', 'spine'),
        'section'    => 'section_spine_advanced_options',
        'settings'   => 'spine_options[broken_binding]',
        'type'       => 'checkbox'
    ));
    
    // Offer Dynamic Shortcuts
    /*$wp_customize->add_setting('spine_options[index_shortcuts]', array(
        'default'        => 'google',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_index_shortcuts', array(
        'label'      => __('Coming: Offer Index Shortcuts', 'spine'),
        'section'    => 'section_spine_advanced_options',
        'settings'   => 'spine_options[index_shortcuts]',
        'type'       => 'checkbox'
    ));

    
    $wp_customize->add_setting('spine_options[local_site_shortcuts]', array(
        'default'        => 'google',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_local_site_shortcuts', array(
        'label'      => __('Offer Site Shortcuts', 'spine'),
        'section'    => 'section_spine_advanced_options',
        'settings'   => 'spine_options[local_site_shortcuts]',
        'type'       => 'checkbox'
    ));
    
    // Local Search
    $wp_customize->add_setting('spine_options[search_local]', array(
        'default'        => 'google',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
 
    $wp_customize->add_control('spine_search_local', array(
        'label'      => __('Coming: Local Search Engine', 'spine'),
        'section'    => 'section_spine_advanced_options',
        'settings'   => 'spine_options[search_local]',
        'type'       => 'radio',
        'choices'    => array(
            'google' => 'Google',
            'wordpress' => 'WordPress'
        ),
    ));*/
    
   // Style Options

    $wp_customize->add_section('section_spine_style', array(
        'title'    => __('Spine: Theme', 'spine'),
        'priority' => 400,
    ));
    
    $wp_customize->add_setting('spine_options[theme_style]', array(
        'default'        => 'bookmark',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
    
    $wp_customize->add_control('spine_theme_style', array(
        'settings'   => 'spine_options[theme_style]',
        'label'      => __('Additional Styling', 'spine'),
        'section'    => 'section_spine_style',
        'type'       => 'select',
        'choices'    => array(
            'skeletal' => 'Skeletal (none)',
            'bookmark' => 'Bookmark'
        ),
    ));
    
    $wp_customize->add_setting('spine_options[secondary_colors]', array(
        'default'        => 'gray',
        'capability'     => 'edit_theme_options',
        'type'           => 'option',
    ));
    
    $wp_customize->add_control('spine_secondary_colors', array(
        'settings'   => 'spine_options[secondary_colors]',
        'label'      => __('Secondary Colors', 'spine'),
        'section'    => 'section_spine_style',
        'type'       => 'select',
        'choices'    => array(
            'gray' => 'Gray (none)',
            'green' => 'Green',
            'orange' => 'Orange',
            'blue' => 'Blue',
            'yellow' => 'Yellow'
        ),
    ));
 
}
 
add_action('customize_register', 'spine_customize_register');

?>

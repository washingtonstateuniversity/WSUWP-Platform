<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js no-svg lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]><html class="no-js no-svg lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]><html class="no-js no-svg lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->

<?php // CUSTOMIZATION
	$spine_options = get_option( 'spine_options' );

	// Defaults for the spine options will be compared to what is stored in spine_options.
	$defaults = array(
		'grid_style'     => 'hybrid',
		'spine_color'    => 'white',
		'large_format'   => '',
		'theme_style'   => 'bookmark',
		'broken_binding' => false,
	);
	$spine_options = wp_parse_args( $spine_options, $defaults );

	$grid_style = $spine_options['grid_style'];
	$theme_style = $spine_options['theme_style'];
	$spine_color = $spine_options['spine_color'];
	$large_format = $spine_options['large_format'];
	$binder_broken = $spine_options['broken_binding'];
	if ( isset($binder_broken) && ($binder_broken == true)) { $binder_broken = " broken"; } else { $binder_broken = ""; }
	?>

<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<title><?php wp_title( '|', true, 'right' ); ?> Washington State University</title>
	
	<!-- Stylesheet to incorporate into queue -->
	<link href="<?php echo get_template_directory_uri().'/styles/'.$theme_style.'.css';?>" rel="stylesheet" type="text/css" />
	
	<!-- FAVICON -->
	<link rel="shortcut icon" href="//repo.wsu.edu/spine/1/favicon.ico" />
	
	<!-- RESPOND -->
	<meta name="viewport" content="width=device-width, user-scalable=yes">
	
	<!-- DOCS -->
	<link type="text/plain" rel="author" href="//repo.wsu.edu/spine/1/authors.txt" />
	<link type="text/html" rel="help" href="http://brand.wsu.edu/media/web" />
	
	<!-- ANALYTICS -->
	<!-- Your analytics code here -->
	
	<?php wp_head(); ?>

	<!-- COMPATIBILITY -->
	<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<noscript><style>#spine #spine-sitenav ul ul li { display: block !important; }</style></noscript>
</head>

<body <?php body_class(); ?>>

<div id="jacket" class="style-<?php echo esc_attr( $theme_style ); ?>">
<div id="binder" class="<?php echo esc_attr( $grid_style ); echo esc_attr( $large_format ); echo esc_attr( $binder_broken ); ?>">
<?php

/* Template Name: Blank */
// Provides simply an unmodified <main> container

?>

<?php get_header(); ?>

<main class="spine-blank-template">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php the_content(); ?>
	</div><!-- #post -->

<?php endwhile; endif; ?>

</main>

<?php get_footer(); ?>
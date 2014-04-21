<?php get_header(); ?>

<main class="spine-main-index">

<?php get_template_part('parts/headers'); ?> 

<section class="row sidebar">

	<div class="column one">
	
		<?php // Introductory Article
		if ( ( get_post_status('1') == 'publish' ) && ( get_the_title('1') == 'Hello world!') ) { get_template_part( 'includes/startup/welcome' ); }  ?>
	
		<?php while ( have_posts() ) : the_post(); ?>
				
			<?php get_template_part('articles/post'); ?>

		<?php endwhile; // end of the loop. ?>

	</div><!--/column-->

	<div class="column two">
		
		<?php get_sidebar(); ?>
		
	</div><!--/column two-->

</section>

</main><!--/#page-->

<?php get_footer(); ?>
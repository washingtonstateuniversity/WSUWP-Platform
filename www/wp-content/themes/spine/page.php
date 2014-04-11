<?php get_header(); ?>

<main class="spine-page-default">

<?php get_template_part('parts/headers'); ?> 

<section class="row sidebar">

	<div class="column one">
	
		<?php while ( have_posts() ) : the_post(); ?>
	
			<?php get_template_part('articles/article'); ?>
		
		<?php endwhile; ?>
		
	</div><!--/column-->

	<div class="column two">
		
		<?php get_sidebar(); ?>
		
	</div><!--/column two-->

</section>

</main>

<?php get_footer(); ?>
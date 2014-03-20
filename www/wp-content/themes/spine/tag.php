<?php get_header(); ?>

<main>

<?php get_template_part('parts/headers'); ?> 

<section class="row sidebar">

	<div class="column one">
	
		<?php while ( have_posts() ) : the_post(); ?>
				
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>
			<?php the_content(); ?>
		</article>

		<?php endwhile; // end of the loop. ?>
		
	</div><!--/column-->

	<div class="column two">
		
		<?php get_sidebar(); ?>
		
	</div><!--/column two-->

</section>

</main><!--/#page-->

<?php get_footer(); ?>
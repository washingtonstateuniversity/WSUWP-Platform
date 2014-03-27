<?php /* Template Name: Single */ ?>

<?php get_header(); ?>

<main class="spine-single-template">

<?php if ( have_posts() ) : while( have_posts() ) : the_post(); ?>

<?php get_template_part('parts/headers'); ?>

<section class="row single">

	<div class="column one">

		<?php get_template_part('articles/article'); ?>
		
	</div><!--/column-->

</section>
<?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
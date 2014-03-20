<?php get_header(); ?>

<main>
	
	<header class="bookmark">
		<hgroup>
		    <div class="site"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></div>
		    <div class="page">Page Not Found</div>
		</hgroup>
	</header>
	
	<section class="row">
	
	<div class="column one">
	
	<article id="post-0" class="post error404 no-results not-found">
		<header class="entry-header">
			<h1 class="entry-title"><?php _e( 'This is somewhat embarrassing, isn&rsquo;t it?', 'twentytwelve' ); ?></h1>
		</header>

		<div class="entry-content">
			<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'twentytwelve' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	
	<script>
		/* $(document).ready(function(){
			$('#wsu-search').clone().appendTo('main');
		}); */
	
	</script>
	
	</article>
	
	</div>
	
	</section>

</main>
	
<?php get_footer(); ?>
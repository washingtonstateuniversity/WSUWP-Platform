<!-- This article greets new installers of the Spine until they change the default post and explains how to get started with the Spine -->
<article>
	
	<h2>Welcome to the WSU Web</h2>
	
	<p>As a visual element, the WSU Spine is a 198px wide column that binds together the many websites of wsu.edu. As a framework, the WSU Spine is a minimal template that provides global tools and a responsive and flexible grid for every WSU website. With a uniform and global spine on the left and a blank, unwritten page to the right, the Spine balances the unity and diversity of our university.</p>
	
	<img src="<?php echo esc_url( get_template_directory_uri() . '/admin/customizer.png' ); ?>" class="alignright">
	<h2>Getting Started</h2>
	
	<ol>
		<li>After <a href="<?php echo esc_url( wp_login_url() ); ?>">logging in</a>, head to the <a href="<?php echo esc_url( admin_url( 'customize.php?theme=spine' ) ); ?>">Customizer</a>.</li>
		<li>Enter your Site Title and Tagline.</li>
		<li>Expand "Contact Details" and enter the information of the unit responsible for this site.</li>
		<li>Optionally, you can replace or remove one or more of the university's social channels.</li>
		<li>Optionally, you can alter the Spine's default behavior in "Spine Options".</li>
		<li>Head to <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Pages</a> and <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ) ; ?>">Appearance -> Menus</a> to begin building out your site.</li>
		<li>And finally, delete or modify your <a href="<?php echo esc_url( admin_url( 'post.php?post=1&action=edit' ) ); ?>">Hello World post</a> to remove this primer.</li>
	</ol>
	
</article>
<?php // Settings

	$spine_options = get_option( 'spine_options' );

	$social = array();
	
	if ( isset($spine_options['social_spot_one_type']) && $spine_options['social_spot_one_type'] != "none" ) {
		$key = $spine_options['social_spot_one_type']; $social[$key] = $spine_options['social_spot_one'];
		}
	// else { $url = "http://facebook.com/wsupullman"; }
	if ( isset($spine_options['social_spot_two_type']) && $spine_options['social_spot_two_type'] != "none" ) {
		 $key = $spine_options['social_spot_two_type']; $social[$key] = $spine_options['social_spot_two'];
		 }
	// else { $url = "http://facebook.com/wsupullman"; }
	if (
		isset($spine_options['social_spot_three_type']) && $spine_options['social_spot_three_type'] != "none" ) {
		$key = $spine_options['social_spot_three_type']; $social[$key] = $spine_options['social_spot_three'];
		}
	// else { $social[0] = "youtube"; $url = "http://youtube.com/washingtsonstateuniv"; }
	if ( isset($spine_options['social_spot_four_type']) && $spine_options['social_spot_four_type'] != "none" ) {
		$key = $spine_options['social_spot_four_type']; $social[$key] = $spine_options['social_spot_four'];
		}
	// else { $social[0] = "facebook"; $url = "http://facebook.com/wsupullman"; }

?>

<footer>

<nav id="wsu-social-channels">
	
	<ul>
	<?php 
		// var_dump($social);
		foreach($social as $socialite=>$url) {
		echo '<li class="'.$socialite.'-channel"><a href="'.$url.'">'.$socialite.'</a></li>';
	} ?>

	</ul>
</nav>
<nav id="wsu-global-links">
	<ul>
		<li class="zzusis-link"><a href="http://zzusis.wsu.edu/">Zzusis</a></li>
		<li class="access-link"><a href="http://access.wsu.edu/">Access</a></li>
		<li class="policies-link"><a href="http://policies.wsu.edu/">Policies</a></li>
		<li class="copyright-link"><a href="http://copyright.wsu.edu">&copy;</a></li>
	</ul>
</nav>	

</footer>
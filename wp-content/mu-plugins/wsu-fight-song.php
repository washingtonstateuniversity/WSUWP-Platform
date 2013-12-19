<?php
/*
Plugin Name: WSU Fight Song
Plugin URI: http://web.wsu.edu
Description: A fork of Hello Dolly containing the WSU Fight Song lyrics.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

function wsu_fight_song_get_lyric() {
	$lyrics = "Fight, fight, fight for Washington State!
Win the victory!
Win the day for Crimson and Gray!
Best in the West, we know you'll all do your best, so
On, on, on, on!
Fight to the end!
Honor and Glory you must win! So
Fight, fight, fight for Washington State and victory!
W-A-S-H-I-N-G-T-O-N-S-T-A-T-E-C-O-U-G-S!
GO COUGS!!";

	$lyrics = explode( "\n", $lyrics );

	return wptexturize( $lyrics[ mt_rand( 0, count( $lyrics ) - 1 ) ] );
}

add_action( 'admin_notices', 'wsu_fight_song' );
function wsu_fight_song() {
	$lyric = wsu_fight_song_get_lyric();
	echo '<p id="wsu-fight">' . $lyric . '</p>';
}

add_action( 'admin_head', 'wsu_fight_song_css' );
function wsu_fight_song_css() {
	// This makes sure that the positioning is also good for right-to-left languages
	$x = is_rtl() ? 'left' : 'right';

	echo "
	<style type='text/css'>
	#wsu-fight {
		float: $x;
		padding-$x: 15px;
		padding-top: 5px;
		margin: 0;
		font-size: 11px;
	}
	</style>
	";
}

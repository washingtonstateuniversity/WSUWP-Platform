<?php
/*
Plugin Name: WSUWP Dashboard
Plugin URI: http://web.wsu.edu/
Description: Modifications to the WordPress Dashboard.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSUWP_WordPress_Dashboard {

	/**
	 * Add our hooks.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		add_action( 'wp_network_dashboard_setup', array( $this, 'remove_network_dashboard_widgets' ) );
		add_filter( 'update_footer', array( $this, 'update_footer_text' ), 11 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 11 );
		add_action( 'in_admin_footer', array( $this, 'display_shield_in_footer' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_stylesheet' ) );
	}

	/**
	 * Enqueue styles specific to the network admin dashboard.
	 */
	public function enqueue_dashboard_stylesheet() {
		if ( 'dashboard-network' === get_current_screen()->id ) {
			wp_enqueue_style( 'wsuwp-dashboard-style', plugins_url( '/css/dashboard-network.css', __FILE__ ), array(), wsuwp_global_version() );
		}
	}

	/**
	 * Remove all of the dashboard widgets and panels when a user logs
	 * in except for the Right Now area.
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_incoming_links' , 'dashboard', 'normal' );
		remove_meta_box( 'tribe_dashboard_widget'   , 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins'        , 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary'        , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_secondary'      , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_quick_press'    , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_recent_drafts'  , 'dashboard', 'side'   );

		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}

	/**
	 * Remove all default widgets from the network dashboard.
	 */
	public function remove_network_dashboard_widgets() {
		remove_meta_box( 'dashboard_plugins'          , 'dashboard-network', 'normal' );
		remove_meta_box( 'dashboard_primary'          , 'dashboard-network', 'side'   );
		remove_meta_box( 'dashboard_secondary'        , 'dashboard-network', 'side'   );

		if ( wsuwp_get_primary_network_id() == wsuwp_get_current_network()->id ) {
			$count_title = 'WSUWP Platform Counts';
		} else {
			$network_name = get_site_option( 'site_name' );
			$count_title = esc_html( $network_name ) . ' Counts';
		}
		wp_add_dashboard_widget( 'dashboard_wsuwp_counts', $count_title, array( $this, 'network_dashboard_counts' ) );

		if ( wsuwp_get_primary_network_id() == wsuwp_get_current_network()->id ) {
			wp_add_dashboard_widget( 'dashboard_wsuwp_memcached', 'Global Memcached Usage', array( $this, 'global_memcached_stats' ) );
		}
	}

	/**
	 * Provide a widget that displays the counts for networks, sites, and users
	 * when viewing the network administration dashboard.
	 */
	public function network_dashboard_counts() {
		if ( wsuwp_get_current_network()->id == wsuwp_get_primary_network_id() ) {
			?>
			<h4>Global</h4>
			<ul class="wsuwp-platform-counts wsuwp-count-above wsuwp-count-thirds">
				<li id="dash-global-networks"><a href="<?php echo esc_url( network_admin_url( 'sites.php?display=network' ) ); ?>"><?php echo wsuwp_network_count(); ?></a></li>
				<li id="dash-global-sites"><a href="<?php echo esc_url( network_admin_url( 'sites.php' ) ); ?>"><?php echo wsuwp_global_site_count(); ?></a></li>
				<li id="dash-global-users"><a href="<?php echo esc_url( network_admin_url( 'users.php' ) ); ?>"><?php echo wsuwp_global_user_count(); ?></a></li>
			</ul>
			<?php
		}
		?>
		<h4>Network</h4>
		<ul class="wsuwp-platform-counts">
			<li id="dash-network-sites"><a href="<?php echo esc_url( network_admin_url( 'sites.php' ) ); ?>"><?php echo esc_html( get_site_option( 'blog_count' ) ); ?></a></li>
			<li id="dash-network-users"><a href="<?php echo esc_url( network_admin_url( 'users.php' ) ); ?>"><?php echo wsuwp_network_user_count( wsuwp_get_current_network()->id ); ?></a></li>
		</ul>
		<?php
	}

	/**
	 * Display a dashboard widget with statistics from the Memcached service.
	 */
	public function global_memcached_stats() {
		$a = new Memcached();
		$a->addServer('localhost', 11211);
		$stats = $a->getStats();
		$stats = $stats['localhost:11211'];

		?>
		<h4>Cache Data</h4>
		<ul class="wsuwp-platform-counts wsuwp-count-above">
			<li id="dash-memcached-written"><?php echo size_format( $stats['bytes_written'] ); ?></li>
			<li id="dash-memcached-read"><?php echo size_format( $stats['bytes_read'] ); ?></li>
		</ul>

		<h4>Cache Hits</h4>
		<ul class="wsuwp-platform-counts wsuwp-count-above">
			<li id="dash-memcached-gets"><?php echo $stats['get_hits']; ?></li>
			<li id="dash-memcached-getsperc"><?php echo ( number_format( 100 * ( $stats['get_hits'] / $stats['cmd_get'] ), 1 ) ); ?>%</li>
		</ul>
		<p>The memcached service has been running for <?php echo human_time_diff( time() - $stats['uptime'], time() ); ?> and
			has handled <?php echo $stats['total_items']; ?> items over <?php echo $stats['total_connections']; ?> connections.</p>
		<p>Currently, <?php echo $stats['curr_connections']; ?> connections are in use and memcached is storing <?php echo $stats['curr_items']; ?>
			items totalling <?php echo size_format( $stats['bytes'] ); ?>.</p>
		<?php
	}

	/**
	 * Customize the update footer text a bit.
	 *
	 * @return string The text to display in the dashboard footer.
	 */
	public function update_footer_text() {
		global $wsuwp_global_version, $wsuwp_wp_changeset;

		$version = ltrim( get_bloginfo( 'version' ), '(' );
		$version = rtrim( $version, ')' );
		$version = explode( '-', $version );

		$text = 'WSUWP Platform <a target=_blank href="https://github.com/washingtonstateuniversity/WSUWP-Platform/tree/v' . $wsuwp_global_version . '">' . $wsuwp_global_version . '</a> | ';
		$text .= 'WordPress ' . $version[0];

		if ( isset( $version[1] ) ) {
			$text .= ' ' . ucwords( $version[1] );
		}

		$text .= ' [<a target=_blank href="https://core.trac.wordpress.org/changeset/' . $wsuwp_wp_changeset . '">' . $wsuwp_wp_changeset . '</a>]';

		return $text;
	}

	/**
	 * Customize the general footer text in the admin.
	 *
	 * @return string
	 */
	public function admin_footer_text() {
		$wp_text = sprintf( __( 'Thank you for creating with <a href="%s">WordPress</a> at <a href="%s">Washington State University</a>.' ), __( 'https://wordpress.org/' ), 'http://wsu.edu' );
		$text = '<span id="footer-thankyou">' . $wp_text . '</span>';

		return $text;
	}

	/**
	 * Display the WSU shield in the footer.
	 */
	public function display_shield_in_footer() {
		echo '<img style="float:left; margin-right:5px;" height="20" src="' . plugins_url( '/images/wsu-shield.png', WPMU_PLUGIN_DIR . '/wsu-dashboard-widgets.php' ) . '" />';
	}
}
$wsuwp_wordpress_dashboard = new WSUWP_WordPress_Dashboard();
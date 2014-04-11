<?php
/*
Plugin Name: WSU SMTP Email
Plugin URI: http://web.wsu.edu/
Description: Use SMTP to send email from WordPress
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

add_action( 'phpmailer_init', 'wsuwp_smtp_email' );
/**
 * Override some default settings during the PHPMailer init process.
 *
 * @param PHPMailer $phpmailer
 */
function wsuwp_smtp_email( $phpmailer ) {
	$phpmailer->Mailer = 'smtp';
	$phpmailer->From = sanitize_email( 'www-data@' . $_SERVER['SERVER_NAME'] );

	if ( is_multisite() ) {
		$phpmailer->FromName = esc_html( get_option( 'blogname' ) ) . ' | ' . esc_html( get_current_site()->site_name );
	} else {
		$phpmailer->FromName = esc_html( get_option( 'blogname' ) );
	}

	$phpmailer->Sender = $phpmailer->From;
	$phpmailer->Host = 'smtp.wsu.edu';
	$phpmailer->Port = 25;
	$phpmailer->SMTPAuth = false;
}
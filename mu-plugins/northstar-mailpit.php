<?php
/**
 * Plugin Name: North Star — Local Mail (Mailpit)
 * Description: Routes all outgoing WordPress email through the local Mailpit
 *              SMTP catcher so you can SEE the emails your site sends while
 *              developing. View them at http://localhost:8025.
 *
 *              This is for LOCAL DEVELOPMENT only. On a live site, install a
 *              real email plugin such as "WP Mail SMTP" and connect it to your
 *              email provider — then delete or disable this file.
 *
 * @package NorthStar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	$host = getenv( 'NS_SMTP_HOST' ) ?: 'mailpit';
	$port = getenv( 'NS_SMTP_PORT' ) ?: 1025;

	$phpmailer->isSMTP();
	$phpmailer->Host        = $host;
	$phpmailer->Port        = (int) $port;
	$phpmailer->SMTPAuth    = false;
	$phpmailer->SMTPAutoTLS = false;
	$phpmailer->SMTPSecure  = '';

	// A clean default From address so mail isn't flagged.
	$phpmailer->From     = 'no-reply@northstartreecare.test';
	$phpmailer->FromName = get_bloginfo( 'name' );
} );

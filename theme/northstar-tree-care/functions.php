<?php
/**
 * North Star Tree Care — theme functions
 *
 * Most day-to-day content (phone, email, address, hero text, social links)
 * is editable from the WordPress dashboard under:
 *   Appearance > Customize > "North Star Settings"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NORTHSTAR_VERSION', '1.0.0' );

/* -------------------------------------------------------------------------
 * Theme setup
 * ---------------------------------------------------------------------- */
function northstar_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 120,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'northstar' ),
		'footer'  => __( 'Footer Menu', 'northstar' ),
	) );
}
add_action( 'after_setup_theme', 'northstar_setup' );

/* -------------------------------------------------------------------------
 * Styles & scripts
 * ---------------------------------------------------------------------- */
function northstar_assets() {
	// Google Fonts — Oswald (headings) + Source Sans 3 (body).
	wp_enqueue_style(
		'northstar-fonts',
		'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'northstar-style', get_stylesheet_uri(), array( 'northstar-fonts' ), NORTHSTAR_VERSION );

	wp_enqueue_script( 'northstar-main', get_template_directory_uri() . '/assets/js/main.js', array(), NORTHSTAR_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'northstar_assets' );

/* -------------------------------------------------------------------------
 * Helper: read a Customizer setting with a sensible default
 * ---------------------------------------------------------------------- */
function northstar_opt( $key, $default = '' ) {
	return get_theme_mod( 'northstar_' . $key, $default );
}

/* -------------------------------------------------------------------------
 * Helper: the brand mark. Uses the WP custom logo if set, otherwise the
 * bundled logo file (logo.png if present, else the SVG fallback).
 * ---------------------------------------------------------------------- */
function northstar_logo( $class = 'brand-mark' ) {
	if ( has_custom_logo() ) {
		echo get_custom_logo();
		return;
	}

	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	if ( file_exists( $dir . '/assets/img/logo.png' ) ) {
		printf(
			'<a class="%1$s-link" href="%2$s"><img class="%1$s" src="%3$s" alt="%4$s"></a>',
			esc_attr( $class ),
			esc_url( home_url( '/' ) ),
			esc_url( $uri . '/assets/img/logo.png' ),
			esc_attr( get_bloginfo( 'name' ) )
		);
		return;
	}

	printf(
		'<a class="%1$s-link" href="%2$s"><img class="%1$s" src="%3$s" alt="%4$s"></a>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		esc_url( $uri . '/assets/img/logo.svg' ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/* -------------------------------------------------------------------------
 * Whether a real image logo is in use (WP custom logo or a bundled logo.png).
 * When true, we hide the separate text wordmark to avoid duplication, since a
 * full logo image usually already contains the business name.
 * ---------------------------------------------------------------------- */
function northstar_using_image_logo() {
	return has_custom_logo() || file_exists( get_template_directory() . '/assets/img/logo.png' );
}

/* -------------------------------------------------------------------------
 * The list of services shown on the home page.
 * Edit the text here, or remove items you don't offer.
 * Icons reference keys defined in northstar_icon().
 * ---------------------------------------------------------------------- */
function northstar_services() {
	return array(
		array( 'icon' => 'axe',     'title' => 'Tree Removal',            'text' => 'Safe, efficient removal of hazardous, dead, or unwanted trees of any size — protecting your home and landscape.',
			'long' => 'When a tree becomes hazardous, diseased, or simply has to go, our crew removes it safely and cleanly. We handle everything from tight residential drops near homes and power lines to large rural takedowns, using proper rigging and equipment to protect everything around it.' ),
		array( 'icon' => 'shears',  'title' => 'Tree Trimming & Pruning', 'text' => 'Expert pruning that promotes healthy growth, strong structure, and great curb appeal for your trees.',
			'long' => 'Proper pruning keeps trees healthy, structurally sound, and looking their best. We remove dead or crossing limbs, raise canopies, thin crowns for light and airflow, and shape young trees so they grow strong for years to come.' ),
		array( 'icon' => 'stump',   'title' => 'Stump Grinding',          'text' => 'Complete stump removal to reclaim your yard and prevent regrowth, tripping hazards, and pests.',
			'long' => 'Leftover stumps are trip hazards and homes for pests. We grind stumps below grade so you can reclaim your yard, replant, or lay new landscaping — and we clean up the grindings when the job is done.' ),
		array( 'icon' => 'storm',   'title' => 'Emergency Storm Cleanup', 'text' => 'Rapid storm response to clear fallen limbs and trees and secure your property when it matters most.',
			'long' => "Storms don't keep business hours, and neither do we. When wind or ice brings limbs or whole trees down, our team responds quickly to clear hazards, free blocked access, and make your property safe again." ),
		array( 'icon' => 'brush',   'title' => 'Land & Brush Clearing',   'text' => 'Clearing overgrown brush and vegetation to create usable, safer, and more attractive land.',
			'long' => 'Overgrown brush, saplings, and undergrowth turn usable land into a liability. We clear lots, fence lines, trails, and acreage to open up your property and reduce fire and pest risk.' ),
		array( 'icon' => 'lot',     'title' => 'Lot Preparation',         'text' => 'Site clearing and prep for new builds, fences, driveways, and landscaping projects.',
			'long' => 'Building, fencing, or landscaping? We clear and prep sites so your project starts on solid ground — removing trees, brush, and debris and leaving a clean, workable lot.' ),
		array( 'icon' => 'leaf',    'title' => 'Property Maintenance',    'text' => 'Year-round upkeep that keeps your landscape safe, clean, healthy, and well managed.',
			'long' => 'Keep your landscape safe and sharp all year. From seasonal cleanups to ongoing tree and vegetation management, we offer dependable maintenance for homes, businesses, and rural properties.' ),
		array( 'icon' => 'hedge',   'title' => 'Shrub & Hedge Care',      'text' => 'Shaping, trimming and care that keep shrubs and hedges healthy, tidy, and beautiful.',
			'long' => 'Healthy, well-shaped shrubs and hedges frame your property beautifully. We trim, shape, and maintain them to keep your landscaping tidy, dense, and thriving.' ),
		array( 'icon' => 'building','title' => 'Commercial & Residential','text' => 'Tailored tree care programs for homes, businesses, HOAs, and municipal properties.',
			'long' => 'From single-family homes to HOAs, businesses, and municipal grounds, we tailor tree care programs to your property, budget, and schedule — with the same safety and quality on every job.' ),
	);
}

/* -------------------------------------------------------------------------
 * Gallery images. Drop real photos (.jpg/.png/.webp) into
 * assets/img/gallery/ and they are used automatically; otherwise the
 * bundled themed placeholders are shown.
 * ---------------------------------------------------------------------- */
function northstar_gallery() {
	$dir = get_template_directory() . '/assets/img/gallery/';
	$uri = get_template_directory_uri() . '/assets/img/gallery/';

	$photos = array();
	foreach ( glob( $dir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE ) as $file ) {
		$name    = basename( $file );
		$caption = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $name, PATHINFO_FILENAME ) ) );
		$photos[] = array( 'img' => $uri . $name, 'caption' => $caption );
	}
	if ( $photos ) {
		return $photos;
	}

	// Bundled placeholders (replace any time with real job photos).
	$ph = array(
		'tree-removal'   => 'Tree Removal',
		'trimming'       => 'Trimming & Pruning',
		'stump-grinding' => 'Stump Grinding',
		'storm-cleanup'  => 'Storm Cleanup',
		'land-clearing'  => 'Land & Brush Clearing',
		'lot-prep'       => 'Lot Preparation',
	);
	$out = array();
	foreach ( $ph as $slug => $caption ) {
		$out[] = array( 'img' => $uri . $slug . '.svg', 'caption' => $caption );
	}
	return $out;
}

/* -------------------------------------------------------------------------
 * "Why choose us" features
 * ---------------------------------------------------------------------- */
function northstar_features() {
	return array(
		array( 'icon' => 'badge',  'title' => 'ISA Industry Standards', 'text' => 'Work performed to International Society of Arboriculture best practices.' ),
		array( 'icon' => 'shield', 'title' => 'Fully Insured',          'text' => 'Licensed and insured crews for complete peace of mind on every job.' ),
		array( 'icon' => 'clock',  'title' => 'Prompt Response',        'text' => 'Fast scheduling and 24/7 availability for storm emergencies.' ),
		array( 'icon' => 'tag',    'title' => 'Transparent Estimates',  'text' => 'Clear, free, no-pressure quotes — no surprises on the invoice.' ),
		array( 'icon' => 'tree',   'title' => 'Certified Arborists',    'text' => 'Professional tree health and risk assessments you can trust.' ),
		array( 'icon' => 'leaf',   'title' => 'Eco-Conscious',          'text' => 'Sustainable disposal, recycling, and tree preservation where possible.' ),
	);
}

/* -------------------------------------------------------------------------
 * Inline SVG icon set (so the theme has zero external image dependencies)
 * ---------------------------------------------------------------------- */
function northstar_icon( $name ) {
	$icons = array(
		'axe'      => '<path d="M14.5 2 9 7.5l1.8 1.8-7 7L2 18l2 2 1.7-1.8 7-7 1.8 1.8L20 7.5 14.5 2z"/>',
		'shears'   => '<path d="M7 4a3 3 0 1 0 2.83 4H11l3 3-1.17 1.17A3 3 0 1 0 14 14l8-8V4h-2l-5 5-1.5-1.5L19 2h-3l-4.17 4.17A3 3 0 0 0 7 4zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm5 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>',
		'stump'    => '<path d="M12 2C8 2 5 4.5 5 7.5S8 13 12 13s7-2.5 7-5.5S16 2 12 2zm0 9c-2.8 0-5-1.6-5-3.5S9.2 4 12 4s5 1.6 5 3.5S14.8 11 12 11zm-1 3h2v8h-2z"/>',
		'storm'    => '<path d="M19 11a5 5 0 0 0-9.6-1.8A4 4 0 1 0 8 17h8a4 4 0 0 0 3-6zM11 18l-2 4h2l-1 3 4-5h-2l1-2z"/>',
		'brush'    => '<path d="M3 21c3-1 5-3 6-6l3 3c-2 3-5 4-9 5v-2zm9.5-7L19 7.5 16.5 5 10 11.5 12.5 14zM18 4l2 2 1.5-1.5a1.4 1.4 0 0 0-2-2L18 4z"/>',
		'lot'      => '<path d="M3 20V8l9-5 9 5v12h-6v-6H9v6H3zm2-2h2v-6h10v6h2V9.2l-7-3.9-7 3.9V18z"/>',
		'leaf'     => '<path d="M5 21c0-7 4-13 14-13 0 9-5 14-12 14 0 0 0-4 4-7-3 1-5 3-6 6z"/>',
		'hedge'    => '<path d="M4 20v-6a4 4 0 0 1 4-4V8a4 4 0 0 1 8 0v2a4 4 0 0 1 4 4v6H4zm8-12a2 2 0 0 0-2 2v8h4v-8a2 2 0 0 0-2-2z"/>',
		'building' => '<path d="M3 21V3h10v6h8v12H3zm2-2h6V5H5v14zm8 0h6v-8h-6v8zM7 7h2v2H7V7zm0 4h2v2H7v-2zm0 4h2v2H7v-2z"/>',
		'badge'    => '<path d="m12 2 2.6 1.8 3.1-.3 1 3 2.5 1.9-1.2 2.9 1.2 2.9-2.5 1.9-1 3-3.1-.3L12 22l-2.6-1.8-3.1.3-1-3L2.8 15.6 4 12.7l-1.2-2.9 2.5-1.9 1-3 3.1.3L12 2zm-1.2 12.8 5-5-1.4-1.4-3.6 3.6-1.6-1.6-1.4 1.4 3 3z"/>',
		'shield'   => '<path d="M12 2 4 5v6c0 5 3.4 9.2 8 11 4.6-1.8 8-6 8-11V5l-8-3zm-1 14-4-4 1.4-1.4L11 13.2l4.6-4.6L17 10l-6 6z"/>',
		'clock'    => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm1-13h-2v6l5 3 1-1.7-4-2.3V7z"/>',
		'tag'      => '<path d="M2 12 12 2h8v8L10 20l-8-8zm14-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>',
		'tree'     => '<path d="M12 2 6 11h3l-4 6h5v5h4v-5h5l-4-6h3L12 2z"/>',
		'phone'    => '<path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11.4 11.4 0 0 0 3.6.6 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.4a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .6 3.6 1 1 0 0 1-.25 1l-2.15 2.2z"/>',
		'mail'     => '<path d="M2 4h20v16H2V4zm10 7L4 6v1l8 5 8-5V6l-8 5z"/>',
		'pin'      => '<path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/>',
		'facebook' => '<path d="M13 22v-9h3l.5-3.5H13V7.2c0-1 .3-1.7 1.8-1.7H17V2.3A26 26 0 0 0 14.4 2C11.8 2 10 3.6 10 6.5v3H7V13h3v9h3z"/>',
		'instagram'=> '<path d="M12 2c2.7 0 3 0 4.1.1 1 0 1.7.2 2.3.5.6.2 1 .5 1.5 1s.8 1 1 1.5c.3.6.4 1.3.5 2.3 0 1.1.1 1.4.1 4.1s0 3-.1 4.1c0 1-.2 1.7-.5 2.3-.2.6-.5 1-1 1.5s-1 .8-1.5 1c-.6.3-1.3.4-2.3.5-1.1 0-1.4.1-4.1.1s-3 0-4.1-.1c-1 0-1.7-.2-2.3-.5-.6-.2-1-.5-1.5-1s-.8-1-1-1.5c-.3-.6-.4-1.3-.5-2.3C2 15 2 14.7 2 12s0-3 .1-4.1c0-1 .2-1.7.5-2.3.2-.6.5-1 1-1.5s1-.8 1.5-1c.6-.3 1.3-.4 2.3-.5C8.5 2 8.8 2 11.5 2H12zm0 1.8c-2.7 0-3 0-4 .1-.8 0-1.2.2-1.5.3-.4.1-.6.3-.9.6s-.5.5-.6.9c-.1.3-.3.7-.3 1.5-.1 1-.1 1.3-.1 4s0 3 .1 4c0 .8.2 1.2.3 1.5.1.4.3.6.6.9s.5.5.9.6c.3.1.7.3 1.5.3 1 .1 1.3.1 4 .1s3 0 4-.1c.8 0 1.2-.2 1.5-.3.4-.1.6-.3.9-.6s.5-.5.6-.9c.1-.3.3-.7.3-1.5.1-1 .1-1.3.1-4s0-3-.1-4c0-.8-.2-1.2-.3-1.5-.1-.4-.3-.6-.6-.9s-.5-.5-.9-.6c-.3-.1-.7-.3-1.5-.3-1-.1-1.3-.1-4-.1zm0 3.1a5.1 5.1 0 1 1 0 10.2 5.1 5.1 0 0 1 0-10.2zm0 1.8a3.3 3.3 0 1 0 0 6.6 3.3 3.3 0 0 0 0-6.6zm5.3-3.1a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z"/>',
		'star'     => '<path d="m12 2 1.9 6.1H20l-5 3.8 1.9 6.1-5-3.8-5 3.8L8.8 12 4 8.1h6.1L12 2z"/>',
	);

	$path = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['star'];
	return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $path . '</svg>';
}

/* -------------------------------------------------------------------------
 * Lead storage: register a private "Lead" post type so quote requests
 * land in the WordPress admin (Dashboard > Leads), even without email/SMTP.
 * ---------------------------------------------------------------------- */
function northstar_register_lead_cpt() {
	register_post_type( 'ns_lead', array(
		'labels' => array(
			'name'          => __( 'Leads', 'northstar' ),
			'singular_name' => __( 'Lead', 'northstar' ),
			'menu_name'     => __( 'Leads', 'northstar' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 25,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor' ),
		'exclude_from_search' => true,
	) );
}
add_action( 'init', 'northstar_register_lead_cpt' );

/* -------------------------------------------------------------------------
 * Quote form handler (admin-post). Validates, blocks bots via a honeypot,
 * saves a Lead, attempts to email the business, then redirects back.
 * ---------------------------------------------------------------------- */
function northstar_handle_quote() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	// Honeypot — real users leave this empty.
	if ( ! empty( $_POST['company_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'quote', 'ok', $redirect ) . '#contact' );
		exit;
	}

	if ( ! isset( $_POST['ns_quote_nonce'] ) || ! wp_verify_nonce( $_POST['ns_quote_nonce'], 'ns_quote' ) ) {
		wp_safe_redirect( add_query_arg( 'quote', 'err', $redirect ) . '#contact' );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$service = sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( '' === $name || ( '' === $email && '' === $phone ) ) {
		wp_safe_redirect( add_query_arg( 'quote', 'err', $redirect ) . '#contact' );
		exit;
	}

	$body = sprintf(
		"Name: %s\nEmail: %s\nPhone: %s\nService: %s\n\nMessage:\n%s",
		$name, $email, $phone, $service, $message
	);

	// Store the lead in the dashboard.
	wp_insert_post( array(
		'post_type'    => 'ns_lead',
		'post_status'  => 'publish',
		'post_title'   => sprintf( '%s — %s', $name, $service ?: 'General enquiry' ),
		'post_content' => $body,
	) );

	// Try to email the business owner (works once SMTP is configured).
	$to = northstar_opt( 'email', get_option( 'admin_email' ) );
	wp_mail(
		$to,
		'New quote request — ' . $name,
		$body,
		array( 'Reply-To: ' . ( $email ?: $to ) )
	);

	wp_safe_redirect( add_query_arg( 'quote', 'ok', $redirect ) . '#contact' );
	exit;
}
add_action( 'admin_post_nopriv_ns_quote', 'northstar_handle_quote' );
add_action( 'admin_post_ns_quote', 'northstar_handle_quote' );

/* -------------------------------------------------------------------------
 * Customizer settings
 * ---------------------------------------------------------------------- */
require get_template_directory() . '/inc/customizer.php';

<?php
/**
 * North Star Landscaping — theme functions
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
		array( 'icon' => 'design',  'title' => 'Landscape Design',        'text' => 'Custom landscape designs tailored to your property, style, and budget.',
			'long' => 'Great outdoor spaces start with a great plan. We design custom landscapes — from plant selection and bed layouts to full yard transformations — that fit your home, your taste, and your budget, and look beautiful in every season.' ),
		array( 'icon' => 'mower',   'title' => 'Lawn Care & Maintenance', 'text' => 'Mowing, fertilization, and weed control for a lush, healthy lawn.',
			'long' => 'A healthy lawn takes consistent care. Our maintenance programs include mowing, edging, fertilization, aeration, and weed and pest control to keep your grass thick, green, and the envy of the neighborhood.' ),
		array( 'icon' => 'grass',   'title' => 'Sod & Seeding',           'text' => 'Fresh sod and over-seeding for an instant, even, green lawn.',
			'long' => 'Whether you need a brand-new lawn or are repairing bare, patchy spots, we install quality sod and over-seed for fast, even, healthy growth — properly prepped so it takes root and thrives.' ),
		array( 'icon' => 'leaf',    'title' => 'Mulch & Garden Beds',     'text' => 'Mulching, planting, and bed design that frame your home beautifully.',
			'long' => 'Clean, well-defined beds make a property pop. We design and refresh garden beds, install plants and flowers, and lay fresh mulch that locks in moisture, suppresses weeds, and gives everything a finished look.' ),
		array( 'icon' => 'sapling', 'title' => 'Tree & Shrub Planting',   'text' => 'Selecting and planting the right trees and shrubs for your space.',
			'long' => 'The right plant in the right place pays off for decades. We help select and properly plant trees, shrubs, and perennials suited to your soil and sun, adding privacy, shade, and lasting curb appeal.' ),
		array( 'icon' => 'paver',   'title' => 'Hardscaping & Patios',    'text' => 'Paver patios, walkways, and retaining walls built to last.',
			'long' => 'Turn your yard into an outdoor living space. We design and build paver patios, walkways, fire-pit areas, and retaining walls using quality materials and proper base work that stays level and beautiful for years.' ),
		array( 'icon' => 'drop',    'title' => 'Irrigation Systems',      'text' => 'Efficient sprinkler systems that keep your landscape green.',
			'long' => 'Stop dragging hoses around. We install and service efficient irrigation and sprinkler systems with smart zoning and timers so your lawn and beds get exactly the water they need — and not a drop wasted.' ),
		array( 'icon' => 'brush',   'title' => 'Seasonal Cleanups',       'text' => 'Spring and fall cleanups, leaf removal, and bed refreshes.',
			'long' => 'Keep your property sharp year-round. Our spring and fall cleanups handle leaf and debris removal, bed edging, pruning, and mulch refreshes so your landscape always looks its best.' ),
		array( 'icon' => 'snow',    'title' => 'Snow & Ice Management',   'text' => 'Reliable winter snow and ice removal for homes & businesses.',
			'long' => "When winter hits, count on us. We provide dependable snow plowing, shoveling, and ice management for driveways, walkways, and commercial lots — keeping your property safe and accessible all season." ),
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
		'landscape-design' => 'Landscape Design',
		'lawn-care'        => 'Lawn Care',
		'paver-patio'      => 'Paver Patio',
		'garden-beds'      => 'Garden Beds',
		'sod-install'      => 'Sod Installation',
		'seasonal-color'   => 'Seasonal Color',
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
		array( 'icon' => 'badge',  'title' => 'Quality Craftsmanship', 'text' => 'Meticulous work and premium materials on every project, big or small.' ),
		array( 'icon' => 'shield', 'title' => 'Licensed & Insured',    'text' => 'Fully insured crews for complete peace of mind on your property.' ),
		array( 'icon' => 'design', 'title' => 'Free Design Consults',  'text' => 'No-pressure consultations and clear, written estimates up front.' ),
		array( 'icon' => 'clock',  'title' => 'Reliable & On Time',    'text' => 'We show up, communicate clearly, and finish when we say we will.' ),
		array( 'icon' => 'sapling','title' => 'Healthy Plants',        'text' => 'Hardy, locally-suited plants and turf chosen to thrive for years.' ),
		array( 'icon' => 'leaf',   'title' => 'Eco-Friendly Care',     'text' => 'Smart watering, organic options, and sustainable practices.' ),
	);
}

/* -------------------------------------------------------------------------
 * Customer testimonials shown on the home page.
 * Edit the text here to use your real reviews.
 * ---------------------------------------------------------------------- */
function northstar_testimonials() {
	return array(
		array(
			'quote' => 'North Star completely transformed our backyard — new patio, beds, and a lush lawn. It looks like a magazine cover now. Worth every penny.',
			'name'  => 'Sarah M.',
			'role'  => 'Homeowner',
		),
		array(
			'quote' => 'Their weekly lawn care is fantastic. The grass has never looked greener and the crew is always professional and on time.',
			'name'  => 'David R.',
			'role'  => 'Residential Client',
		),
		array(
			'quote' => 'We use North Star for landscaping and snow removal across our properties. Reliable, insured, and the results always impress our tenants.',
			'name'  => 'Property Management Co.',
			'role'  => 'Commercial Client',
		),
	);
}

/* -------------------------------------------------------------------------
 * Service areas (towns/regions you cover). Editable as a comma-separated
 * list under Appearance > Customize > North Star Settings, with a sensible
 * placeholder list as the default.
 * ---------------------------------------------------------------------- */
function northstar_service_areas() {
	$raw = northstar_opt( 'areas', '' );
	if ( '' !== trim( (string) $raw ) ) {
		return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
	}
	return array(
		'Downtown & Metro', 'North Side', 'South Side', 'East Suburbs',
		'West Suburbs', 'Surrounding Rural Areas', 'Lake Communities', 'Nearby Counties',
	);
}

/* -------------------------------------------------------------------------
 * Build a keyless Google Maps embed URL from the configured location.
 * ---------------------------------------------------------------------- */
function northstar_map_embed_src() {
	$q = northstar_opt( 'map_query', 'United States' );
	return 'https://maps.google.com/maps?q=' . rawurlencode( $q ) . '&z=10&ie=UTF8&iwloc=&output=embed';
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
		'design'   => '<path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v3h6V5H5zm8 0v3h6V5h-6zM5 10v9h6v-9H5zm8 0v9h6v-9h-6z"/>',
		'mower'    => '<path d="M11 2h3.6L13 11h6a1 1 0 0 1 1 1v3h-1.1a3 3 0 0 1-5.8 0H9.9a3 3 0 0 1-5.8 0H3a1 1 0 0 1-1-1 3 3 0 0 1 3-3h6l1.6-7H11V2zM6 14a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm10 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>',
		'grass'    => '<path d="M12 22c-.3-4 .4-7 2-9-.2 1.2-.1 2.3.3 3.4.6-1.5 1.6-2.7 3-3.4-1.5 2-2.2 4.9-2.1 9H12zM9.8 22H7c0-4.3-.7-7-2.1-9 1.4.7 2.4 1.9 3 3.4.4-1.1.5-2.2.3-3.4 1.6 2 2.3 5 1.6 9z"/>',
		'sapling'  => '<path d="M11 22v-7h2v7h-2zm1-8c-3 0-5-2-5-5 3 0 5 2 5 5zm0-2c0-3 2-5 5-5 0 3-2 5-5 5z"/>',
		'paver'    => '<path d="M3 4h18v4H3V4zm0 6h8v4H3v-4zm10 0h8v4h-8v-4zM3 16h18v4H3v-4z"/>',
		'drop'     => '<path d="M12 2s7 7.6 7 12a7 7 0 1 1-14 0c0-4.4 7-12 7-12zm0 16a4 4 0 0 0 4-4h-2a2 2 0 0 1-2 2v2z"/>',
		'snow'     => '<path d="M11 1h2v22h-2z"/><path d="M1 11h22v2H1z"/><path d="M3.2 4.6 4.6 3.2 20.8 19.4l-1.4 1.4z"/><path d="M19.4 3.2l1.4 1.4L4.6 20.8l-1.4-1.4z"/>',
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
	$to = northstar_opt( 'email', 'Support@north-star-pros.com' );
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
 * First-run bootstrap. When the theme is activated (e.g. after uploading it
 * to Hostinger or any WordPress host), this creates the site pages, sets the
 * static front page, and builds + assigns the navigation menu — so the site
 * is ready without needing the Docker setup script. Runs once.
 * ---------------------------------------------------------------------- */
function northstar_bootstrap_content() {
	if ( get_option( 'northstar_setup_done' ) ) {
		return;
	}

	$pages = array(
		'home'          => 'Home',
		'services'      => 'Services',
		'gallery'       => 'Gallery',
		'service-areas' => 'Service Areas',
		'about'         => 'About',
		'contact'       => 'Contact',
	);

	$ids = array();
	foreach ( $pages as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = (int) $existing->ID;
			continue;
		}
		$ids[ $slug ] = (int) wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => '',
		) );
	}

	// Use the designed Home page as the static front page.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	// Build and assign the primary navigation menu (once).
	if ( ! wp_get_nav_menu_object( 'Main Menu' ) ) {
		$menu_id = wp_create_nav_menu( 'Main Menu' );
		if ( ! is_wp_error( $menu_id ) ) {
			$labels = array(
				'home' => 'Home', 'services' => 'Services', 'gallery' => 'Gallery',
				'service-areas' => 'Areas', 'about' => 'About', 'contact' => 'Contact',
			);
			foreach ( $labels as $slug => $label ) {
				if ( empty( $ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $label,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// Pretty permalinks so /services/ etc. resolve (only if still "Plain").
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules( false );
		}
	}

	update_option( 'northstar_setup_done', 1 );
}
add_action( 'after_switch_theme', 'northstar_bootstrap_content' );

/* -------------------------------------------------------------------------
 * Customizer settings
 * ---------------------------------------------------------------------- */
require get_template_directory() . '/inc/customizer.php';

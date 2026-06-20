<?php
/**
 * North Star Settings — adds an easy "North Star Settings" panel to
 * Appearance > Customize so the owner can change business details without
 * touching code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function northstar_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'northstar_settings', array(
		'title'       => __( 'North Star Settings', 'northstar' ),
		'priority'    => 30,
		'description' => __( 'Business contact details, hero text, and social links used across the site.', 'northstar' ),
	) );

	$fields = array(
		'phone'        => array( 'label' => 'Phone number',            'default' => '(989) 318-4491',                          'type' => 'text' ),
		'phone_link'   => array( 'label' => 'Phone (dial format)',     'default' => '+19893184491',                            'type' => 'text' ),
		'email'        => array( 'label' => 'Email address',           'default' => 'Support@north-star-pros.com',              'type' => 'text' ),
		'address'      => array( 'label' => 'Service area / address',  'default' => 'Serving residential & commercial properties', 'type' => 'text' ),
		'hours'        => array( 'label' => 'Hours / availability',    'default' => 'Mon–Sat 7am–6pm · Free Estimates', 'type' => 'text' ),
		'map_query'    => array( 'label' => 'Map location (town, state or address)', 'default' => 'United States', 'type' => 'text' ),
		'areas'        => array( 'label' => 'Service areas (comma-separated towns)',  'default' => '',              'type' => 'text' ),
		'hero_tagline' => array( 'label' => 'Hero tagline',            'default' => 'Where Quality Takes Root.', 'type' => 'text' ),
		'hero_lead'    => array( 'label' => 'Hero intro text',         'default' => 'Professional landscape design, lawn care, hardscaping, and seasonal maintenance for residential and commercial properties.', 'type' => 'textarea' ),
		'facebook'     => array( 'label' => 'Facebook URL',            'default' => '',                                        'type' => 'url' ),
		'instagram'    => array( 'label' => 'Instagram URL',           'default' => '',                                        'type' => 'url' ),
	);

	$priority = 10;
	foreach ( $fields as $key => $field ) {
		$setting = 'northstar_' . $key;
		$sanitize = 'url' === $field['type'] ? 'esc_url_raw' : ( 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field' );

		$wp_customize->add_setting( $setting, array(
			'default'           => $field['default'],
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( $setting, array(
			'label'    => __( $field['label'], 'northstar' ),
			'section'  => 'northstar_settings',
			'type'     => 'textarea' === $field['type'] ? 'textarea' : ( 'url' === $field['type'] ? 'url' : 'text' ),
			'priority' => $priority,
		) );

		$priority += 5;
	}
}
add_action( 'customize_register', 'northstar_customize_register' );

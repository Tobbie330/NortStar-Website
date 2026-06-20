<?php
/**
 * Header + fixed navigation
 */
$phone      = northstar_opt( 'phone', '(555) 123-4567' );
$phone_link = northstar_opt( 'phone_link', '+15551234567' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
	<div class="header-inner">
		<div class="brand">
			<?php northstar_logo( 'brand-mark' ); ?>
			<?php if ( ! northstar_using_image_logo() ) : ?>
				<div class="brand-text">
					<strong><?php bloginfo( 'name' ); ?></strong>
					<span><?php echo esc_html( 'Care Today. Grow Tomorrow.' ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<nav class="primary-nav" id="primary-nav" aria-label="Primary">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '<ul>%3$s</ul>',
					'depth'          => 1,
				) );
			} else {
				// Default menu (used until a custom menu is assigned in WP admin).
				echo '<ul>';
				echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/services/' ) ) . '">Services</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/gallery/' ) ) . '">Gallery</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">About</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">Contact</a></li>';
				echo '</ul>';
			}
			?>
		</nav>

		<div class="header-cta">
			<a class="header-phone" href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a>
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Free Quote</a>
			<button class="nav-toggle" id="nav-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="primary-nav">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</header>

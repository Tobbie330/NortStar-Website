<?php
/**
 * Service Areas page (auto-used for the page with slug "service-areas").
 * The map location and the list of towns are editable under
 * Appearance > Customize > North Star Settings.
 */
get_header();

$address = northstar_opt( 'address', 'Serving residential, commercial & municipal properties' );
$phone   = northstar_opt( 'phone', '(989) 318-4490' );

while ( have_posts() ) : the_post();
?>
<section class="page-hero">
	<div class="container">
		<span class="eyebrow">Where We Work</span>
		<h1><?php the_title(); ?></h1>
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);"><?php echo esc_html( $address ); ?></p>
	</div>
</section>

<?php if ( trim( get_the_content() ) ) : ?>
<section class="section" style="padding-bottom:0;">
	<div class="container" style="max-width:820px;"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<section class="section">
	<div class="container areas-layout">
		<div class="areas-copy">
			<span class="eyebrow">Proudly Serving</span>
			<h2>Local, Reliable Tree Care Near You</h2>
			<p>We provide tree removal, trimming, stump grinding, storm cleanup, and property maintenance throughout the communities below. Don't see your area? Give us a call — we likely cover it.</p>
			<ul class="areas-grid">
				<?php foreach ( northstar_service_areas() as $area ) : ?>
					<li class="area-chip"><?php echo northstar_icon( 'pin' ); ?><span><?php echo esc_html( $area ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Check My Area</a>
		</div>
		<div class="areas-map">
			<div class="map-embed">
				<iframe
					title="Service area map"
					src="<?php echo esc_url( northstar_map_embed_src() ); ?>"
					width="600" height="450" style="border:0;"
					loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>
			<p class="map-tip">Set your real location in <em>Appearance → Customize → North Star Settings → Map location</em>.</p>
		</div>
	</div>
</section>

<section class="section section--dark" style="text-align:center;">
	<div class="container" style="max-width:720px;">
		<span class="eyebrow">Fast Response</span>
		<h2>Need Tree Work in Your Neighborhood?</h2>
		<p style="color:rgba(244,241,232,.8);">Call <a href="tel:<?php echo esc_attr( northstar_opt( 'phone_link', '+19893184490' ) ); ?>" style="color:var(--ns-olive-bright);"><?php echo esc_html( $phone ); ?></a> or request a free estimate online.</p>
		<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get a Free Quote</a>
	</div>
</section>

<?php
endwhile;
get_footer();

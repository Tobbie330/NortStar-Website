<?php
/**
 * Contact page (auto-used for the page with slug "contact").
 */
get_header();

$phone      = northstar_opt( 'phone', '(989) 318-4491' );
$phone_link = northstar_opt( 'phone_link', '+19893184491' );
$email      = northstar_opt( 'email', 'Support@north-star-pros.com' );
$address    = northstar_opt( 'address', 'Serving residential & commercial properties' );
$hours      = northstar_opt( 'hours', 'Mon–Sat 7am–6pm · Free Estimates' );

while ( have_posts() ) : the_post();
?>
<section class="page-hero">
	<div class="container">
		<span class="eyebrow">Get In Touch</span>
		<h1><?php the_title(); ?></h1>
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);">Request a free, no-pressure estimate — we respond fast.</p>
	</div>
</section>

<section class="section section--dark" style="padding-top:64px;">
	<div class="container contact-grid">
		<div class="contact-info">
			<span class="eyebrow">Contact Details</span>
			<h2>We're Ready to Help</h2>
			<p>Ready to transform your yard or keep it looking its best? Call us or send the form and we'll get right back to you.</p>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="contact-extra" style="color:rgba(244,241,232,.8);"><?php the_content(); ?></div>
			<?php endif; ?>
			<ul class="contact-list">
				<li>
					<span class="ci-icon"><?php echo northstar_icon( 'phone' ); ?></span>
					<div><strong>Call Us</strong><a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a></div>
				</li>
				<li>
					<span class="ci-icon"><?php echo northstar_icon( 'mail' ); ?></span>
					<div><strong>Email</strong><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
				</li>
				<li>
					<span class="ci-icon"><?php echo northstar_icon( 'pin' ); ?></span>
					<div><strong>Service Area</strong><span><?php echo esc_html( $address ); ?></span></div>
				</li>
				<li>
					<span class="ci-icon"><?php echo northstar_icon( 'clock' ); ?></span>
					<div><strong>Hours</strong><span><?php echo esc_html( $hours ); ?></span></div>
				</li>
			</ul>
		</div>

		<?php get_template_part( 'template-parts/quote-form' ); ?>
	</div>
</section>

<section class="map-band" aria-label="Service area">
	<div class="map-overlay">
		<div class="container">
			<?php echo northstar_icon( 'pin' ); ?>
			<p><?php echo esc_html( $address ); ?></p>
			<small>Add a Google Map embed here later from Pages → Contact in the WordPress editor.</small>
		</div>
	</div>
</section>

<?php
endwhile;
get_footer();

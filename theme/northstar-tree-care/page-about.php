<?php
/**
 * About page (auto-used for the page with slug "about").
 */
get_header();
while ( have_posts() ) : the_post();
?>
<section class="page-hero">
	<div class="container">
		<span class="eyebrow">Who We Are</span>
		<h1><?php the_title(); ?></h1>
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);">Rooted in quality. Guided by the North Star.</p>
	</div>
</section>

<section class="section">
	<div class="container about-grid">
		<div class="about-copy">
			<span class="eyebrow">Our Story</span>
			<h2>Tree Care Built on Integrity &amp; Hard Work</h2>
			<p class="lead">North Star Tree Care is a professional tree and property maintenance company dedicated to keeping residential, commercial, and rural properties safe, healthy, and beautiful.</p>
			<p>We specialize in expert tree removal, precision trimming, stump grinding, storm damage cleanup, and emergency tree services. Our experienced team combines safety, reliability, and industry-leading practices to protect your property while enhancing its natural beauty.</p>
			<p>Whether you need routine maintenance, hazardous tree removal, land clearing, or a complete property cleanup, we deliver dependable service with a commitment to quality workmanship and customer satisfaction.</p>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="about-extra"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
		<div class="about-stats">
			<div class="stat"><strong>100%</strong><span>Licensed &amp; Insured Work</span></div>
			<div class="stat"><strong>24/7</strong><span>Emergency Storm Response</span></div>
			<div class="stat"><strong>9+</strong><span>Specialized Services</span></div>
			<div class="stat"><strong>ISA</strong><span>Industry Standards</span></div>
		</div>
	</div>
</section>

<section class="section section--tint">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">What Drives Us</span>
			<h2>Our Mission &amp; Values</h2>
		</div>
		<div class="values-grid">
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'shield' ); ?></div>
				<h3>Safety First</h3>
				<p>Every job is planned and executed to protect your property, our crew, and the public.</p>
			</div>
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'badge' ); ?></div>
				<h3>Quality Workmanship</h3>
				<p>We follow ISA best practices and take pride in clean, professional results.</p>
			</div>
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'clock' ); ?></div>
				<h3>Dependability</h3>
				<p>We show up, communicate clearly, and finish the job right — on time.</p>
			</div>
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'leaf' ); ?></div>
				<h3>Stewardship</h3>
				<p>We preserve trees where we can and dispose of debris responsibly.</p>
			</div>
		</div>
	</div>
</section>

<section class="section mission">
	<div class="container">
		<img class="star-divider" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/star.svg' ); ?>" alt="">
		<span class="eyebrow">Our Mission</span>
		<blockquote>"Providing safe, professional, and dependable tree care services while helping our communities grow stronger, safer, and more beautiful — one property at a time."</blockquote>
		<cite>Rooted in Quality. Guided by the North Star.</cite>
		<div style="margin-top:30px;">
			<a class="btn btn--light btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Work With Us</a>
		</div>
	</div>
</section>

<?php
endwhile;
get_footer();

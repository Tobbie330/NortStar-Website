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
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);">Where quality takes root.</p>
	</div>
</section>

<section class="section">
	<div class="container about-grid">
		<div class="about-copy">
			<span class="eyebrow">Our Story</span>
			<h2>Landscaping Built on Quality &amp; Hard Work</h2>
			<p class="lead">North Star Landscaping is a full-service landscaping company dedicated to designing, building, and maintaining healthy, beautiful outdoor spaces for residential and commercial properties.</p>
			<p>We specialize in landscape design, lawn care, sod and seeding, mulch and garden beds, hardscaping, irrigation, and seasonal cleanups. Our experienced crews combine quality craftsmanship with reliable service to boost your property's curb appeal and value.</p>
			<p>Whether you want a brand-new landscape, a lush lawn, a new paver patio, or year-round maintenance, we deliver dependable results with a commitment to quality workmanship and customer satisfaction.</p>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="about-extra"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
		<div class="about-stats">
			<div class="stat"><strong>100%</strong><span>Licensed &amp; Insured Work</span></div>
			<div class="stat"><strong>Free</strong><span>On-Site Estimates</span></div>
			<div class="stat"><strong>9+</strong><span>Landscaping Services</span></div>
			<div class="stat"><strong>5★</strong><span>Rated Local Service</span></div>
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
				<p>We follow industry best practices and take pride in clean, professional results.</p>
			</div>
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'clock' ); ?></div>
				<h3>Dependability</h3>
				<p>We show up, communicate clearly, and finish the job right — on time.</p>
			</div>
			<div class="value-card">
				<div class="service-icon"><?php echo northstar_icon( 'leaf' ); ?></div>
				<h3>Stewardship</h3>
				<p>We use sustainable, eco-friendly practices and tidy, responsible cleanup.</p>
			</div>
		</div>
	</div>
</section>

<section class="section mission">
	<div class="container">
		<img class="star-divider" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/star.svg' ); ?>" alt="">
		<span class="eyebrow">Our Mission</span>
		<blockquote>"Creating beautiful, healthy outdoor spaces that bring lasting value and enjoyment to every property and community we serve — one yard at a time."</blockquote>
		<cite>Where Quality Takes Root.</cite>
		<div style="margin-top:30px;">
			<a class="btn btn--light btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Work With Us</a>
		</div>
	</div>
</section>

<?php
endwhile;
get_footer();

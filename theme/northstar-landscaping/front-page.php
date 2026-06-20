<?php
/**
 * Front page — the North Star Landscaping one-page landing experience.
 */
get_header();

$phone        = northstar_opt( 'phone', '(989) 318-4491' );
$phone_link   = northstar_opt( 'phone_link', '+19893184491' );
$email        = northstar_opt( 'email', 'Support@north-star-pros.com' );
$address      = northstar_opt( 'address', 'Serving residential & commercial properties' );
$hours        = northstar_opt( 'hours', 'Mon–Sat 7am–6pm · Free Estimates' );
$hero_tagline = northstar_opt( 'hero_tagline', 'Where Quality Takes Root.' );
$hero_lead    = northstar_opt( 'hero_lead', 'Professional landscape design, lawn care, hardscaping, and seasonal maintenance for residential and commercial properties.' );

$quote_status = isset( $_GET['quote'] ) ? sanitize_key( $_GET['quote'] ) : '';
$services     = northstar_services();
$star_svg     = get_template_directory_uri() . '/assets/img/star.svg';
?>

<!-- ============================ HERO ============================ -->
<section class="hero" id="top">
	<div class="container hero-inner">
		<img class="hero-star" src="<?php echo esc_url( $star_svg ); ?>" alt="North Star compass">
		<p class="hero-tagline"><?php echo esc_html( $hero_tagline ); ?></p>
		<h1>Beautiful Landscapes<span class="accent">Year-Round.</span></h1>
		<p class="hero-lead"><?php echo esc_html( $hero_lead ); ?></p>
		<div class="hero-actions">
			<a class="btn btn--primary btn--lg" href="#contact">Get a Free Quote</a>
			<a class="btn btn--ghost btn--lg" href="tel:<?php echo esc_attr( $phone_link ); ?>">
				<?php echo northstar_icon( 'phone' ); ?> Call <?php echo esc_html( $phone ); ?>
			</a>
		</div>
		<div class="hero-trust">
			<span><?php echo northstar_icon( 'shield' ); ?> Licensed &amp; Insured</span>
			<span><?php echo northstar_icon( 'design' ); ?> Free Estimates</span>
			<span><?php echo northstar_icon( 'badge' ); ?> Quality Guaranteed</span>
		</div>
	</div>
</section>

<!-- ============================ ABOUT ============================ -->
<section class="section" id="about">
	<div class="container about-grid">
		<div class="about-copy">
			<span class="eyebrow">Who We Are</span>
			<h2>Landscaping Done Beautifully, Done Right.</h2>
			<p class="lead">North Star Landscaping is a full-service landscaping company dedicated to designing, building, and maintaining healthy, beautiful outdoor spaces for residential and commercial properties.</p>
			<p>We specialize in landscape design, lawn care, sod and seeding, mulch and garden beds, hardscaping, irrigation, and seasonal cleanups. Our experienced crews combine quality craftsmanship with reliable service to enhance your property's curb appeal and value.</p>
			<p>Whether you want a brand-new landscape design, a lush, well-kept lawn, a new paver patio, or year-round maintenance, we deliver dependable results with a commitment to quality workmanship and customer satisfaction.</p>
			<a class="btn btn--primary" href="#services">Explore Our Services</a>
		</div>
		<div class="about-stats">
			<div class="stat"><strong>100%</strong><span>Licensed &amp; Insured</span></div>
			<div class="stat"><strong>Free</strong><span>On-Site Estimates</span></div>
			<div class="stat"><strong>9+</strong><span>Landscaping Services</span></div>
			<div class="stat"><strong>5★</strong><span>Rated Local Service</span></div>
		</div>
	</div>
</section>

<!-- ============================ SERVICES ============================ -->
<section class="section section--tint" id="services">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">What We Do</span>
			<h2>Our Landscaping Services</h2>
			<p>From a fresh lawn to a full backyard transformation, we have the crew and equipment to bring your vision to life.</p>
		</div>
		<div class="services-grid">
			<?php foreach ( $services as $s ) : ?>
				<article class="service-card">
					<div class="service-icon"><?php echo northstar_icon( $s['icon'] ); ?></div>
					<h3><?php echo esc_html( $s['title'] ); ?></h3>
					<p><?php echo esc_html( $s['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ WHY US ============================ -->
<section class="section section--dark" id="why">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">Why North Star</span>
			<h2>Quality You Can See.</h2>
			<p>Property owners choose us for professional service, dependable scheduling, and beautiful, long-lasting results.</p>
		</div>
		<div class="why-grid">
			<?php foreach ( northstar_features() as $f ) : ?>
				<div class="feature">
					<div class="feature-icon"><?php echo northstar_icon( $f['icon'] ); ?></div>
					<div>
						<h3><?php echo esc_html( $f['title'] ); ?></h3>
						<p><?php echo esc_html( $f['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ MISSION ============================ -->
<section class="section mission">
	<div class="container">
		<img class="star-divider" src="<?php echo esc_url( $star_svg ); ?>" alt="">
		<span class="eyebrow">Our Mission</span>
		<blockquote>"Creating beautiful, healthy outdoor spaces that bring lasting value and enjoyment to every property and community we serve — one yard at a time."</blockquote>
		<cite>Where Quality Takes Root.</cite>
	</div>
</section>

<!-- ============================ PROCESS ============================ -->
<section class="section" id="process">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">How It Works</span>
			<h2>Simple, Transparent Process</h2>
			<p>No surprises — just clear communication from the first call to final cleanup.</p>
		</div>
		<div class="steps-grid">
			<div class="step"><h3>Free Estimate</h3><p>Call or request a quote online. We assess the job and provide a clear, written estimate.</p></div>
			<div class="step"><h3>Plan &amp; Schedule</h3><p>We agree on scope, timing, and safety plan that fits your property and your calendar.</p></div>
			<div class="step"><h3>Expert Execution</h3><p>Our insured crew completes the work safely using professional equipment and techniques.</p></div>
			<div class="step"><h3>Full Cleanup</h3><p>We haul away debris and leave your property clean, safe, and looking its best.</p></div>
		</div>
	</div>
</section>

<!-- ============================ TESTIMONIALS ============================ -->
<section class="section section--tint" id="testimonials">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">Reviews</span>
			<h2>What Our Customers Say</h2>
			<p>We've earned a reputation for professional, reliable landscaping and beautiful results.</p>
		</div>
		<div class="testimonials-grid">
			<?php foreach ( northstar_testimonials() as $t ) : ?>
				<figure class="testimonial">
					<div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
					<blockquote><?php echo esc_html( $t['quote'] ); ?></blockquote>
					<figcaption>
						<strong><?php echo esc_html( $t['name'] ); ?></strong>
						<span><?php echo esc_html( $t['role'] ); ?></span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ CONTACT ============================ -->
<section class="section section--dark" id="contact">
	<div class="container contact-grid">
		<div class="contact-info">
			<span class="eyebrow">Get In Touch</span>
			<h2>Request Your Free Quote</h2>
			<p>Ready to upgrade your lawn or outdoor space? Reach out today — we respond fast and the estimate is always free.</p>
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

<?php get_footer(); ?>

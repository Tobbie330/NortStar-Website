<?php
/**
 * Front page — the North Star Tree Care one-page landing experience.
 */
get_header();

$phone        = northstar_opt( 'phone', '(555) 123-4567' );
$phone_link   = northstar_opt( 'phone_link', '+15551234567' );
$email        = northstar_opt( 'email', 'info@northstartreecare.com' );
$address      = northstar_opt( 'address', 'Serving residential, commercial & municipal properties' );
$hours        = northstar_opt( 'hours', 'Mon–Sat 7am–6pm · 24/7 Storm Emergencies' );
$hero_tagline = northstar_opt( 'hero_tagline', 'Rooted in Quality. Guided by the North Star.' );
$hero_lead    = northstar_opt( 'hero_lead', 'Professional tree removal, trimming, stump grinding and emergency storm care for residential, commercial, and rural properties.' );

$quote_status = isset( $_GET['quote'] ) ? sanitize_key( $_GET['quote'] ) : '';
$services     = northstar_services();
$star_svg     = get_template_directory_uri() . '/assets/img/star.svg';
?>

<!-- ============================ HERO ============================ -->
<section class="hero" id="top">
	<div class="container hero-inner">
		<img class="hero-star" src="<?php echo esc_url( $star_svg ); ?>" alt="North Star compass">
		<p class="hero-tagline"><?php echo esc_html( $hero_tagline ); ?></p>
		<h1>Expert Tree Care<span class="accent">You Can Trust.</span></h1>
		<p class="hero-lead"><?php echo esc_html( $hero_lead ); ?></p>
		<div class="hero-actions">
			<a class="btn btn--primary btn--lg" href="#contact">Get a Free Quote</a>
			<a class="btn btn--ghost btn--lg" href="tel:<?php echo esc_attr( $phone_link ); ?>">
				<?php echo northstar_icon( 'phone' ); ?> Call <?php echo esc_html( $phone ); ?>
			</a>
		</div>
		<div class="hero-trust">
			<span><?php echo northstar_icon( 'shield' ); ?> Licensed &amp; Insured</span>
			<span><?php echo northstar_icon( 'badge' ); ?> ISA Best Practices</span>
			<span><?php echo northstar_icon( 'clock' ); ?> 24/7 Storm Response</span>
		</div>
	</div>
</section>

<!-- ============================ ABOUT ============================ -->
<section class="section" id="about">
	<div class="container about-grid">
		<div class="about-copy">
			<span class="eyebrow">Who We Are</span>
			<h2>Tree Care Done Safely, Done Right.</h2>
			<p class="lead">North Star Tree Care is a professional tree and property maintenance company dedicated to keeping residential, commercial, and rural properties safe, healthy, and beautiful.</p>
			<p>We specialize in expert tree removal, precision trimming, stump grinding, storm damage cleanup, and emergency tree services. Our experienced team combines safety, reliability, and industry-leading practices to protect your property while enhancing its natural beauty.</p>
			<p>Whether you need routine tree maintenance, hazardous tree removal, land clearing, or complete property cleanup, we deliver dependable service with a commitment to quality workmanship and customer satisfaction — guided by integrity and hard work.</p>
			<a class="btn btn--primary" href="#services">Explore Our Services</a>
		</div>
		<div class="about-stats">
			<div class="stat"><strong>100%</strong><span>Licensed &amp; Insured Work</span></div>
			<div class="stat"><strong>24/7</strong><span>Emergency Storm Response</span></div>
			<div class="stat"><strong>9+</strong><span>Specialized Services</span></div>
			<div class="stat"><strong>ISA</strong><span>Industry Standards</span></div>
		</div>
	</div>
</section>

<!-- ============================ SERVICES ============================ -->
<section class="section section--tint" id="services">
	<div class="container">
		<div class="section-head">
			<span class="eyebrow">What We Do</span>
			<h2>Our Tree &amp; Property Services</h2>
			<p>From a single hazardous limb to clearing an entire lot, we have the crew and equipment to get it done safely.</p>
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
			<h2>Reliable Crews. Honest Work.</h2>
			<p>Property owners choose us for professional service, prompt response times, and uncompromising safety standards.</p>
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
		<blockquote>"Providing safe, professional, and dependable tree care services while helping our communities grow stronger, safer, and more beautiful — one property at a time."</blockquote>
		<cite>Rooted in Quality. Guided by the North Star.</cite>
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

<!-- ============================ CONTACT ============================ -->
<section class="section section--dark" id="contact">
	<div class="container contact-grid">
		<div class="contact-info">
			<span class="eyebrow">Get In Touch</span>
			<h2>Request Your Free Quote</h2>
			<p>Have a tree that needs attention or storm damage to clean up? Reach out today — we respond fast and the estimate is always free.</p>
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

		<form class="quote-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<h3>Tell Us About Your Project</h3>
			<p class="form-sub">We'll get back to you with a free, no-pressure estimate.</p>

			<?php if ( 'ok' === $quote_status ) : ?>
				<div class="form-alert form-alert--ok">Thanks! Your request has been received — we'll be in touch shortly.</div>
			<?php elseif ( 'err' === $quote_status ) : ?>
				<div class="form-alert form-alert--err">Sorry, something went wrong. Please add your name and a phone or email, then try again.</div>
			<?php endif; ?>

			<input type="hidden" name="action" value="ns_quote">
			<?php wp_nonce_field( 'ns_quote', 'ns_quote_nonce' ); ?>
			<!-- Honeypot: hidden from humans -->
			<div class="hp-field" aria-hidden="true">
				<label>Company Website</label>
				<input type="text" name="company_website" tabindex="-1" autocomplete="off">
			</div>

			<div class="form-row">
				<div class="field">
					<label for="qf-name">Name *</label>
					<input id="qf-name" type="text" name="name" required>
				</div>
				<div class="field">
					<label for="qf-phone">Phone</label>
					<input id="qf-phone" type="tel" name="phone">
				</div>
			</div>
			<div class="form-row">
				<div class="field">
					<label for="qf-email">Email</label>
					<input id="qf-email" type="email" name="email">
				</div>
				<div class="field">
					<label for="qf-service">Service Needed</label>
					<select id="qf-service" name="service">
						<option value="">Select a service…</option>
						<?php foreach ( $services as $s ) : ?>
							<option value="<?php echo esc_attr( $s['title'] ); ?>"><?php echo esc_html( $s['title'] ); ?></option>
						<?php endforeach; ?>
						<option value="Other">Other / Not sure</option>
					</select>
				</div>
			</div>
			<div class="field">
				<label for="qf-message">Project Details</label>
				<textarea id="qf-message" name="message" placeholder="Tell us what you need — number of trees, location, timing, etc."></textarea>
			</div>
			<button type="submit" class="btn btn--primary btn--lg" style="width:100%; justify-content:center;">Send My Request</button>
			<p class="form-note">* Please provide your name and at least a phone or email so we can reach you.</p>
		</form>
	</div>
</section>

<?php get_footer(); ?>

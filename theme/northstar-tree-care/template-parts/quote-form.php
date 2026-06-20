<?php
/**
 * Reusable "Request a Quote" form. Used on the home page and the Contact page.
 * Self-contained: reads its own success/error state and service list.
 */
$quote_status = isset( $_GET['quote'] ) ? sanitize_key( $_GET['quote'] ) : '';
$services     = northstar_services();
?>
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

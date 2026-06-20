<?php
/**
 * Footer
 */
$phone      = northstar_opt( 'phone', '(555) 123-4567' );
$phone_link = northstar_opt( 'phone_link', '+15551234567' );
$email      = northstar_opt( 'email', 'info@northstartreecare.com' );
$address    = northstar_opt( 'address', 'Serving residential, commercial & municipal properties' );
$facebook   = northstar_opt( 'facebook' );
$instagram  = northstar_opt( 'instagram' );
$home       = home_url( '/' );
?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-top">
			<div class="footer-brand">
				<?php northstar_logo( 'brand-mark' ); ?>
				<p><?php esc_html_e( 'Professional tree removal, trimming, stump grinding and emergency storm care. Keeping residential, commercial and rural properties safe, healthy and beautiful.', 'northstar' ); ?></p>
				<?php if ( $facebook || $instagram ) : ?>
					<div class="footer-social">
						<?php if ( $facebook ) : ?><a href="<?php echo esc_url( $facebook ); ?>" aria-label="Facebook" target="_blank" rel="noopener"><?php echo northstar_icon( 'facebook' ); ?></a><?php endif; ?>
						<?php if ( $instagram ) : ?><a href="<?php echo esc_url( $instagram ); ?>" aria-label="Instagram" target="_blank" rel="noopener"><?php echo northstar_icon( 'instagram' ); ?></a><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<h4>Services</h4>
				<ul>
					<li><a href="<?php echo esc_url( $home . '#services' ); ?>">Tree Removal</a></li>
					<li><a href="<?php echo esc_url( $home . '#services' ); ?>">Trimming &amp; Pruning</a></li>
					<li><a href="<?php echo esc_url( $home . '#services' ); ?>">Stump Grinding</a></li>
					<li><a href="<?php echo esc_url( $home . '#services' ); ?>">Storm Cleanup</a></li>
					<li><a href="<?php echo esc_url( $home . '#services' ); ?>">Land Clearing</a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h4>Contact</h4>
				<ul>
					<li><a href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<li><?php echo esc_html( $address ); ?></li>
				</ul>
			</div>
		</div>

		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</span>
			<span><?php esc_html_e( 'Rooted in Quality. Guided by the North Star.', 'northstar' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

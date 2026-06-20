<?php
/**
 * Services page (auto-used for the page with slug "services").
 */
get_header();
while ( have_posts() ) : the_post();
?>
<section class="page-hero">
	<div class="container">
		<span class="eyebrow">What We Do</span>
		<h1><?php the_title(); ?></h1>
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);">Full-service tree and property care for residential, commercial, and rural properties — handled safely, cleanly, and on schedule.</p>
	</div>
</section>

<?php if ( trim( get_the_content() ) ) : ?>
<section class="section" style="padding-bottom:0;">
	<div class="container" style="max-width:820px;"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<section class="section">
	<div class="container">
		<div class="service-list">
			<?php foreach ( northstar_services() as $i => $s ) : ?>
				<article class="service-detail" id="service-<?php echo (int) $i; ?>">
					<div class="service-detail-icon"><?php echo northstar_icon( $s['icon'] ); ?></div>
					<div class="service-detail-body">
						<h2><?php echo esc_html( $s['title'] ); ?></h2>
						<p><?php echo esc_html( isset( $s['long'] ) ? $s['long'] : $s['text'] ); ?></p>
						<a class="text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Request a quote for <?php echo esc_html( $s['title'] ); ?> →</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section mission" style="padding:70px 0;">
	<div class="container">
		<img class="star-divider" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/star.svg' ); ?>" alt="">
		<blockquote style="font-size:clamp(1.4rem,3vw,2rem);">Not sure which service you need? We'll take a look and recommend the right plan — the estimate is always free.</blockquote>
		<a class="btn btn--light btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get a Free Quote</a>
	</div>
</section>

<?php
endwhile;
get_footer();

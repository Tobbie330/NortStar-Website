<?php
/**
 * Gallery page (auto-used for the page with slug "gallery").
 * Images come from northstar_gallery() — drop real photos into
 * assets/img/gallery/ to replace the placeholders.
 */
get_header();
while ( have_posts() ) : the_post();
?>
<section class="page-hero">
	<div class="container">
		<span class="eyebrow">Our Work</span>
		<h1><?php the_title(); ?></h1>
		<p style="max-width:640px;margin:0 auto;color:rgba(244,241,232,.8);">A look at the kind of tree and property work we do every day. Quality you can see.</p>
	</div>
</section>

<?php if ( trim( get_the_content() ) ) : ?>
<section class="section" style="padding-bottom:0;">
	<div class="container" style="max-width:820px;"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<section class="section">
	<div class="container">
		<div class="gallery-grid" id="gallery">
			<?php foreach ( northstar_gallery() as $g ) : ?>
				<figure class="gallery-item">
					<a href="<?php echo esc_url( $g['img'] ); ?>" class="gallery-link" data-caption="<?php echo esc_attr( $g['caption'] ); ?>">
						<img src="<?php echo esc_url( $g['img'] ); ?>" alt="<?php echo esc_attr( $g['caption'] ); ?>" loading="lazy">
						<figcaption><?php echo esc_html( $g['caption'] ); ?></figcaption>
					</a>
				</figure>
			<?php endforeach; ?>
		</div>
		<p class="gallery-note">Want to see your project here? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get in touch</a> for a free estimate.</p>
	</div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" aria-hidden="true">
	<button class="lightbox-close" aria-label="Close">&times;</button>
	<figure class="lightbox-inner">
		<img src="" alt="" id="lightbox-img">
		<figcaption id="lightbox-cap"></figcaption>
	</figure>
</div>

<?php
endwhile;
get_footer();

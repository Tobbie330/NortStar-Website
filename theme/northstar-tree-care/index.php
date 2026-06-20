<?php
/**
 * Fallback template (blog / archive / search).
 */
get_header();
?>
<div class="page-hero">
	<div class="container">
		<h1><?php echo esc_html( is_home() ? 'News & Updates' : get_the_archive_title() ); ?></h1>
	</div>
</div>

<div class="page-body">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?> style="margin-bottom:48px;">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p style="color:var(--ns-muted); font-size:.95rem;"><?php echo esc_html( get_the_date() ); ?></p>
					<?php the_excerpt(); ?>
					<a class="btn btn--primary" href="<?php the_permalink(); ?>">Read more</a>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing here yet. Please check back soon.', 'northstar' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>

<?php
/**
 * Standard page template (About, Services detail, Contact, etc.).
 * Content here is fully editable in the WordPress block editor.
 */
get_header();
?>
<?php while ( have_posts() ) : the_post(); ?>
	<div class="page-hero">
		<div class="container">
			<h1><?php the_title(); ?></h1>
		</div>
	</div>
	<div class="page-body">
		<div class="container">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</div>
<?php endwhile; ?>
<?php get_footer(); ?>

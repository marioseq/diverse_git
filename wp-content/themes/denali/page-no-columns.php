<?php
/**
 * Template Name: No Columns
 *
 */

get_header() ?>

	<?php if(current_theme_supports('inner_page_slideshow_area')): ?>
	<div class="sld-flexible">
		<div class="sld-top"></div>
		<div class="image_wrapper"><?php denali_header_image(); ?></div>
		<div class="sld-bottom"></div>
	</div>
	<?php endif; /* inner_page_slideshow_area */ ?>
	<div id="nocolumns">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class('main-no-sidebar main'); ?>>
				<h1 class="entry-title"><?php the_title();?></h1>
				<div class="entry-content">
				<?php the_content('More Info'); ?>
      			        <?php comments_template( 'comments.php', true ); ?>
				</div>
			</div>
		<?php endwhile; endif; ?>
	<div class="cboth"></div>
	</div><!-- #noclumns -->
<?php get_footer() ?>

 

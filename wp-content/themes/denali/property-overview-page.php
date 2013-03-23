<?php
/**
 * The default page for property overview page.
 *
 * Used when no WordPress page is setup to display overview via shortcode.
 * Will be rendered as a 404 not-found, but still can display properties.
 *
 * @package WP-Property
 */
global $post, $wp_properties;
get_header(); ?>
	<?php if(current_theme_supports('inner_page_slideshow_area')): ?>
	<div class="sld-flexible">
		<div class="sld-top"></div>
		<div class="image_wrapper"><?php denali_header_image(); ?></div>
		<div class="sld-bottom"></div>
	</div>
	<?php endif; /* inner_page_slideshow_area */ ?>
	<div id="content">

 			<div id="post-property-overview" class="main page type-page hentry main">
				<h1 class="entry-title"><?php echo $post->post_title; ?></h1>
				<div class="entry-content">
						<?php if(is_404()): ?>
							<p>
							<?php _e('Sorry, we could not find what you were looking for.  Since you are here, take a look at some of our properties.','wpp') ?>
							</p>
						<?php endif; ?>
						<?php 
							if($wp_properties[configuration][do_not_override_search_result_page] == 'true')
								echo $content = apply_filters('the_content', $post->post_content); 
						?>
						<?php echo WPP_Core::shortcode_property_overview(); ?>
				</div>
			</div>


	<?php if ( is_active_sidebar( "right_sidebar" ) || is_active_sidebar( "property_overview_sidebar" ) ) : ?>
        <div class="sidebar">
            <?php dynamic_sidebar( 'property_overview_sidebar' ); ?>
            <?php dynamic_sidebar( 'right_sidebar' ); ?>
        </div>
	<?php endif; ?>

        <div class="cboth"></div>

    </div>

<?php get_footer() ?>
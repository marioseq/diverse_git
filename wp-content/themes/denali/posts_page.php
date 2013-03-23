<?php
/**
 * Template for Posts.
 *
 * Typically used to display the "Blog Portion" of a site when used as CMS
 *
 * @version 1.7
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package Denali
*/

	global $wp_query;
	$denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
?>
<?php @get_header() ?>
 
	<?php if(current_theme_supports('post_page_attention_grabber_area') && $denali_theme_settings['post_page_attention_grabber_area_hide'] != 'true'): ?>
    <div class="slide sld-flexible">
    <div class="sld-top"></div>
        <?php if(function_exists('global_slideshow') && $global_slideshow = global_slideshow(true)): ?>
			<?php echo $global_slideshow; ?>		
		<?php else: ?>
		<div class="wpp_slideshow_global_wrapper">
			<?php denali_header_image(); ?>
		</div>
		<?php endif; ?>

		<?php if($denali_theme_settings['hide_slideshow_search_from_posts_page'] != 'true'): ?>
        <div id="global_property_search_home">
            <?php if(is_active_sidebar('global_property_search')) dynamic_sidebar( 'global_property_search' ); ?>
        </div>
		<?php endif; ?>
		<div class="sld-bottom"></div>
    </div>
	<?php endif; /*post_page_attention_grabber_area*/  ?>
	
<div id="content">
    <div class="posts_page <?php if(is_active_sidebar('posts_page_sidebar')): ?>main<?php else: ?>main wide-home<?php endif;?>">

	 <?php get_template_part( 'loop', 'blog' ); ?>

        <div class="cboth"></div>
    </div>
	<?php if(is_active_sidebar('posts_page_sidebar')): ?>
    <div class="sidebar"><?php dynamic_sidebar( 'posts_page_sidebar' ); ?></div>
	<?php endif; ?>
    <div class="cboth"></div>
 </div>
<?php get_footer() ?>

<?php get_header() ?>
	<?php if(current_theme_supports('inner_page_slideshow_area')): ?>
	<div class="sld-flexible">
		<div class="sld-top"></div>
		<div class="image_wrapper"><?php denali_header_image(); ?></div>
		<div class="sld-bottom"></div>
	</div>
	<?php endif; /* inner_page_slideshow_area */ ?>
    <div id="content">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class('main'); ?>>
                <h1 class="entry-title"><?php the_title();?></h1>
                <div class="entry-content">
                    <?php the_content('More Info'); ?>
                    <?php comments_template( 'comments.php', true ); ?>
                </div>
                <?php do_action('denali_page_below_entry_content'); ?>
            </div>
        <?php endwhile; endif; ?>

	<?php if ( is_active_sidebar( "right_sidebar" )  || (is_property_overview_page() && is_active_sidebar( "property_overview_sidebar" ))) : ?>
    <div class="sidebar">
      <?php dynamic_sidebar( 'property_overview_sidebar' ); ?>
      <?php dynamic_sidebar( 'right_sidebar' ); ?>
    </div>
	<?php endif; ?>

    <div class="cboth"></div>

    </div>

<?php get_footer() ?>
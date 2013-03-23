<?php
global $page, $paged;
$denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
$longitude = $denali_theme_settings['longitude'];
$latitude = $denali_theme_settings['latitude'];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php


    wp_title( '|', true, 'right' );

    // Add the blog name.
    bloginfo( 'name' );

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        echo " | $site_description";

    // Add a page number if necessary:
    if ( $paged >= 2 || $page >= 2 )
        echo ' | ' . sprintf( __( 'Page %s', 'denali' ), max( $paged, $page ) );

    ?>
</title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php bloginfo( 'template_directory' ); ?>/favicon.ico" type="image/x-icon" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>> 
<div class="wrapper">	 

  <div id="body_container" class="container">    
           
    <?php  if($denali_theme_settings['hide_logo'] != 'true' ): ?>
		<?php if (!empty($denali_theme_settings['logo'])){ ?>
			<span class="custom_logo"><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><img src="<?php echo $denali_theme_settings['logo']?>" alt="<?php bloginfo('name'); ?>" /><span class="denali_text_logo"><?php echo $denali_theme_settings['logo_text']; ?></span></a></span>
		<?php } else { ?>
			<span class="logo"><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><span class="denali_text_logo"><?php echo $denali_theme_settings['logo_text']; ?></span></a></span>
		<?php } ?> 
	<?php endif; ?>
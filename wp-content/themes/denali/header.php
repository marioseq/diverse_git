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
 <div class="disbl" style="display:none; overflow:hidden">
    
	<?php if(current_theme_supports('header-property-contact')): ?>
	<div id="div-1" class='header_dropdown_div header_contact_div'>
        <ul>
            <li class="continfo">
                <?php echo (!empty($denali_theme_settings['name']) ? "<h5>" . $denali_theme_settings['name'] . "</h5>" : ""); ?>
                <?php echo (!empty($denali_theme_settings['info']) ? "<p class='denali_header_info'>" . $denali_theme_settings['info'] . "</p>" : ""); ?>

                <div class="cboth"></div>
                
                <?php echo (!empty($denali_theme_settings['latitude']) ? "<div id='map_canvas'></div>" : ""); ?>
                
                <p class="contact_info">
                    <?php echo (!empty($denali_theme_settings['name']) ? "<span class='sena'>" . $denali_theme_settings['name'] . "</span><br />" : ""); ?>
                    <?php echo (!empty($denali_theme_settings['address']) ? $denali_theme_settings['address'] .'<br />' : ""); ?>
                    <?php echo (!empty($denali_theme_settings['phone']) ? $denali_theme_settings['phone'] .'<br />' : ""); ?>
                 </p>
            </li>
            
            <li class="form">
                <form action="#" id="denali_contact_form" method="post">
                <div class="ajax_error hidden"></div>
                <div class = "contact">
					<div id = "contact_left">
						<label for="contact_name"><?php _e("Name", "wpp"); ?>: <span>*</span></label>
                        <input   id="contact_name"  type="text" />
					</div>
						<div id = "contact_right">
							<label for="contact_email"><?php _e("E-mail", "wpp"); ?>: <span>*</span></label>
                            <input  id="contact_email" type="text" />
						</div>
						<div id="contact_foot">
								<label for="contact_message"><?php _e("Message", "wpp"); ?>: <span>*</span></label>
                                <textarea id="contact_message" class="requiredField"></textarea>
						</div>
					<input type="submit" name="submitContact" id="submitContact" value="<?php _e("Send Message", "wpp"); ?>" />		
                </div>
                </form>
            </li>
            <li class="cboth"></li>
        </ul>
    </div>
	<?php endif; ?>
    <?php if(!is_user_logged_in()) { ?>
        <div id="div-2" class="header_dropdown_div">
            <ul>
                <li class="continfo">
                    <?php echo (!empty($denali_theme_settings[name]) ? "<h5>{$denali_theme_settings[name]}</h5>"  : "") ?>
                    <?php echo (!empty($denali_theme_settings[info]) ? "<p class='denali_header_info'>{$denali_theme_settings[info]}</p>"  : "") ?>
                    <?php echo (!empty($denali_theme_settings[name]) ? "<p><span class='sena'>{$denali_theme_settings[address]}</span><br />"  : "") ?>
                    <?php echo (!empty($denali_theme_settings[address]) ? "{$denali_theme_settings[address]} <br />"  : "") ?>
                    <?php echo (!empty($denali_theme_settings[phone]) ? "Phone: {$denali_theme_settings[phone]} <br />"  : "") ?>
                </li>
                <li class="form">
                    <?php 
						$current_page = (is_singular() ? get_permalink($post->ID) : get_bloginfo('url'));
						
						wp_login_form(array('redirect' => $current_page, 'form_id' => 'header_login_form')); 
					?>
                </li>
                <li class="cboth"></li>
            </ul>
        </div>
    <?php } ?>
	 
	<?php if(current_theme_supports('header-property-search')): ?>
     <div id="div-3" class="header_dropdown_div">
        <?php if(is_active_sidebar('global_property_search')) dynamic_sidebar( 'global_property_search' ); ?>
        <div class="cboth"></div>
    </div>
	<?php endif; ?>
 
</div>
<div class="wrapper">
	<div class="body_upper_background"></div>
    <div class="mid">
        <ul class="log_menu">
        
		<?php if(current_theme_supports('header-property-search')): ?>
            <?php if(!substr_count(get_page_template(), 'content.php') &&  is_active_sidebar('global_property_search')){ ?>
                <li id="3" class="find_top dropdown_tab_find_property"><a href="#"><?php _e('Find your property', 'wpp'); ?></a></li>
            <?php } ?>
		<?php endif; ?>
		
		<?php if(current_theme_supports('header-property-contact')): ?>
		<?php if($denali_theme_settings['hide_header_contact'] != 'true'): ?>
            <li id="1" class="dropdown_tab_contact_us"><a href="#"><?php _e('Contact us', 'wpp'); ?></a></li>
		<?php endif; ?>
		<?php endif; ?>
		
		<?php if(current_theme_supports('header-login')): ?>
            <?php if($denali_theme_settings['hide_header_login'] != 'true' && !is_user_logged_in()) { ?>
                <li id="2" class="option_tab dropdown_tab_login"><a href="#"><?php _e('Login'); ?></a></li>
            <?php } ?>
		<?php endif; ?>
		
             <?php edit_post_link( __('Edit', 'wpp'), "<li class='edit_post option_tab'>", "</li>"); ?> 
            <?php wp_register('<li class="reg option_tab">', '</li>'); ?></li>
        </ul>
		
		<?php if(current_theme_supports('header-card')): ?>
			<div class="header_business_card">
			<?php echo (!empty($denali_theme_settings['phone']) ? "<span class='phone'>" . apply_filters("denali_call_us_text", __('call us', 'wpp')) . " <span class='number'>{$denali_theme_settings['phone']}</span></span>": "");?>
			<?php echo ($denali_theme_settings['hide_address_from_card'] != 'true' && !empty($denali_theme_settings['address']) ? " <span class='address'>{$denali_theme_settings['address']}</span>": "");?>
			</div>
		<?php endif; ?>
       
       
    <?php  if($denali_theme_settings['hide_logo'] != 'true' ): ?>
		<?php if (!empty($denali_theme_settings['logo'])){ ?>
			<span class="custom_logo"><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><img src="<?php echo $denali_theme_settings['logo']?>" alt="<?php bloginfo('name'); ?>" /><span class="denali_text_logo"><?php echo $denali_theme_settings['logo_text']; ?></span></a></span>
		<?php } else { ?>
			<span class="logo"><a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><span class="denali_text_logo"><?php echo $denali_theme_settings['logo_text']; ?></span></a></span>
		<?php } ?> 
	<?php endif; ?>
      
    </div>

  <div id="body_container" class="container">
    <div class="midd">
    <?php wp_nav_menu(apply_filters('denali_header_menu', array(
      'theme_location'=> 'header-menu',
      'menu_class'    => 'main-nav',
      'link_before'   => '<span class="menu"><span class="link_text">',
      'link_after'    => '</span></span>',
      'depth'         => 2  )));
    ?>    
    <?php wp_nav_menu(apply_filters('denali_sub_header_menu', array(
      'theme_location'=> 'header-sub-menu',
      'menu_class'    => 'header-sub-menu',
      'link_before'   => '<span class="menu"><span class="link_text">',
      'link_after'    => '</span></span>',
      'fallback_cb'    => false,
      'depth'         => 2  )));
    ?>
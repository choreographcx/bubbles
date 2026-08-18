<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="bpr-navbar sticky-top">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="<?php echo esc_url(home_url('/')); ?>">
                <?php if (has_custom_logo()) { the_custom_logo(); } else { ?><img class="bpr-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/bubbles-logo.png'); ?>" alt="<?php bloginfo('name'); ?>"><?php } ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bprMenu" aria-controls="bprMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="bprMenu">
                <?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'menu_class' => 'navbar-nav ms-auto align-items-lg-center gap-lg-2', 'fallback_cb' => 'bpr_default_menu', 'depth' => 2)); ?>
            </div>
        </div>
    </nav>
</header>
<?php
function bpr_default_menu() {
    echo '<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2"><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/dogs/')) . '">Dogs</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/cats/')) . '">Cats</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/adoption-application/')) . '">Adopt</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/foster-application/')) . '">Foster</a></li></ul>';
}

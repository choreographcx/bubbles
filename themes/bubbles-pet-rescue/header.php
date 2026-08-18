<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="bpr-skip-link" href="#bpr-main"><?php esc_html_e('Skip to content', 'bubbles-pet-rescue'); ?></a>
<header class="bpr-navbar sticky-top">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo esc_url(home_url('/')); ?>">
                <img class="bpr-brand-mark" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/bubbles-mark.svg?ver=' . BPR_THEME_VERSION); ?>" alt="">
                <span class="bpr-brand-wordmark"><span class="visually-hidden"><?php bloginfo('name'); ?></span><span aria-hidden="true">Bubbles.Pets<br>Rescue</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#bprMenu" aria-controls="bprMenu" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="offcanvas-lg offcanvas-end bpr-offcanvas" tabindex="-1" id="bprMenu" aria-labelledby="bprMenuLabel">
                <div class="offcanvas-header">
                    <span class="offcanvas-title bpr-section-title h5 mb-0" id="bprMenuLabel"><?php esc_html_e('Menu', 'bubbles-pet-rescue'); ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#bprMenu" aria-label="<?php esc_attr_e('Close', 'bubbles-pet-rescue'); ?>"></button>
                </div>
                <div class="offcanvas-body">
                    <?php wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'menu_class' => 'navbar-nav ms-auto align-items-lg-center gap-lg-2', 'fallback_cb' => 'bpr_default_menu', 'depth' => 2)); ?>
                </div>
            </div>
        </div>
    </nav>
</header>
<?php
function bpr_default_menu() {
    echo '<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2"><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/meet-the-animals/')) . '">Meet the Animals</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/adoption-application/')) . '">Adopt</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/foster-application/')) . '">Foster</a></li><li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/ways-to-help/')) . '">Ways to Help</a></li></ul>';
}

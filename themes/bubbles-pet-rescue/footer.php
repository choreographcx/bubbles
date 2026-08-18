<?php if (!defined('ABSPATH')) { exit; } ?>
<footer class="bpr-footer mt-5">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-start">
            <div class="col-lg-4">
                <img class="bpr-footer-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/bubbles-logo-white.svg'); ?>" alt="">
                <p class="bpr-footer-wordmark">Bubbles.Pets<br>Rescue</p>
                <p class="mb-0">Dedicated to rescuing, rehabilitating, and rehoming stray and abandoned pets across the United Arab Emirates.</p>
            </div>

            <div class="col-6 col-lg-3">
                <h3 class="bpr-footer-heading"><?php esc_html_e('Helpful Links', 'bubbles-pet-rescue'); ?></h3>
                <?php wp_nav_menu(array('theme_location' => 'footer', 'container' => false, 'menu_class' => 'list-unstyled mb-0 bpr-footer-menu')); ?>
            </div>

            <div class="col-6 col-lg-3">
                <h3 class="bpr-footer-heading"><?php esc_html_e('Contact Us', 'bubbles-pet-rescue'); ?></h3>
                <div class="bpr-footer-contact">
                    <div><i class="bi bi-geo-alt-fill" aria-hidden="true"></i><span>Dubai, United Arab Emirates</span></div>
                    <?php $contact_email = get_theme_mod('bpr_contact_email', get_option('admin_email')); ?>
                    <?php if ($contact_email) : ?>
                        <div><i class="bi bi-envelope-fill" aria-hidden="true"></i><a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></div>
                    <?php endif; ?>
                    <?php if ($url = get_theme_mod('bpr_whatsapp_url')) : ?>
                        <div><i class="bi bi-whatsapp" aria-hidden="true"></i><a href="<?php echo esc_url($url); ?>">WhatsApp us</a></div>
                    <?php endif; ?>
                    <?php if ($wishlist = get_theme_mod('bpr_amazon_wishlist_url')) : ?>
                        <div><i class="bi bi-gift-fill" aria-hidden="true"></i><a href="<?php echo esc_url($wishlist); ?>">Amazon Wishlist</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-2">
                <h3 class="bpr-footer-heading"><?php esc_html_e('Follow Us', 'bubbles-pet-rescue'); ?></h3>
                <div class="bpr-social-links">
                    <?php if ($url = get_theme_mod('bpr_instagram_url')) : ?>
                        <a href="<?php echo esc_url($url); ?>" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    <?php endif; ?>
                    <?php if ($url = get_theme_mod('bpr_facebook_url')) : ?>
                        <a href="<?php echo esc_url($url); ?>" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                    <?php endif; ?>
                    <?php if ($url = get_theme_mod('bpr_tiktok_url')) : ?>
                        <a href="<?php echo esc_url($url); ?>" aria-label="TikTok"><i class="bi bi-tiktok" aria-hidden="true"></i></a>
                    <?php endif; ?>
                    <?php if ($url = get_theme_mod('bpr_whatsapp_url')) : ?>
                        <a href="<?php echo esc_url($url); ?>" aria-label="WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr class="border-light opacity-25 my-4">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <p class="small mb-0">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            <?php if ($privacy_url = get_privacy_policy_url()) : ?>
                <p class="small mb-0"><a href="<?php echo esc_url($privacy_url); ?>"><?php esc_html_e('Privacy Policy', 'bubbles-pet-rescue'); ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</footer>
<button type="button" class="bpr-back-to-top" id="bprBackToTop" aria-label="<?php esc_attr_e('Back to top', 'bubbles-pet-rescue'); ?>"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
<?php wp_footer(); ?>
</body>
</html>

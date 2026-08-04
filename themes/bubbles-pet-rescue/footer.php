<?php if (!defined('ABSPATH')) { exit; } ?>
<footer class="bpr-footer mt-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <h2 class="h4 text-white mb-3"><?php bloginfo('name'); ?></h2>
                <p class="mb-3">A UAE rescue community helping pets move from uncertainty into safe foster and forever homes.</p>
                <div class="d-flex gap-3">
                    <?php if ($url = get_theme_mod('bpr_instagram_url')) : ?><a href="<?php echo esc_url($url); ?>"><i class="bi bi-instagram"></i> Instagram</a><?php endif; ?>
                    <?php if ($url = get_theme_mod('bpr_whatsapp_url')) : ?><a href="<?php echo esc_url($url); ?>"><i class="bi bi-whatsapp"></i> WhatsApp</a><?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <h3 class="h6 text-white text-uppercase">Helpful Links</h3>
                <?php wp_nav_menu(array('theme_location' => 'footer', 'container' => false, 'menu_class' => 'list-unstyled mb-0')); ?>
            </div>
            <div class="col-lg-3">
                <h3 class="h6 text-white text-uppercase">Support Through Supplies</h3>
                <p class="mb-3">Help with food, litter, bedding, toys, and medical-care items through the wishlist.</p>
                <?php if ($wishlist = get_theme_mod('bpr_amazon_wishlist_url')) : ?><a class="btn btn-bpr-secondary" href="<?php echo esc_url($wishlist); ?>">Open Wishlist</a><?php endif; ?>
            </div>
        </div>
        <hr class="border-light opacity-25 my-4">
        <p class="small mb-0">&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. Built with care for rescue work in the UAE.</p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

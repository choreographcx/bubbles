<?php get_header(); ?>
<main>
    <section class="bpr-top-wave bpr-hero">
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="bpr-pill mb-3"><i class="bi bi-heart-fill"></i> UAE Pet Rescue</span>
                    <h1 class="display-4 bpr-heading mb-4"><?php echo esc_html(get_theme_mod('bpr_hero_title', 'Helping UAE rescue pets find safe, loving homes')); ?></h1>
                    <p class="bpr-lede mb-4"><?php echo esc_html(get_theme_mod('bpr_hero_text', 'Bubbles Pet Rescue connects dogs and cats with adopters, fosters, and practical support through wishlist items and care supplies.')); ?></p>
                    <div class="d-flex flex-wrap gap-3">
                        <a class="btn btn-bpr-primary" href="<?php echo esc_url(home_url('/dogs/')); ?>">Meet the Dogs</a>
                        <a class="btn btn-bpr-secondary" href="<?php echo esc_url(home_url('/cats/')); ?>">Meet the Cats</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="bpr-card p-4 text-center"><img class="img-fluid rounded-4" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/bubbles-logo.png'); ?>" alt="Bubbles Pet Rescue Logo"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="bpr-section bpr-home-actions">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4"><div class="bpr-card-soft h-100 p-4"><span class="bpr-bubble-icon mb-3"><i class="bi bi-house-heart"></i></span><h2 class="h4 bpr-section-title">Adopt</h2><p>Find a dog or cat who’s ready for a forever family, then submit a simple adoption application.</p><a href="<?php echo esc_url(home_url('/adoption-application/')); ?>">Start an Adoption Application</a></div></div>
                <div class="col-md-4"><div class="bpr-card-soft h-100 p-4"><span class="bpr-bubble-icon mb-3"><i class="bi bi-person-hearts"></i></span><h2 class="h4 bpr-section-title">Foster</h2><p>Offer temporary safety while a rescue pet heals, settles, or waits for the right home.</p><a href="<?php echo esc_url(home_url('/foster-application/')); ?>">Start a Foster Application</a></div></div>
                <div class="col-md-4"><div class="bpr-card-soft h-100 p-4"><span class="bpr-bubble-icon mb-3"><i class="bi bi-gift"></i></span><h2 class="h4 bpr-section-title">Wishlist Needs</h2><p>Support practical needs such as food, litter, bedding, crates, toys, and care supplies.</p><?php if ($wishlist = get_theme_mod('bpr_amazon_wishlist_url')) : ?><a href="<?php echo esc_url($wishlist); ?>">Open Amazon Wishlist</a><?php else : ?><a href="<?php echo esc_url(home_url('/wishlist/')); ?>">View Current Needs</a><?php endif; ?></div></div>
            </div>
        </div>
    </section>

    <?php
    $featured_pet_id = bpr_get_home_featured_pet_id();
    if ($featured_pet_id) :
        $featured_name = get_the_title($featured_pet_id);
        $featured_url = get_permalink($featured_pet_id);
        $featured_image_id = bpr_get_pet_primary_image_id($featured_pet_id);
        $featured_breed = bpr_get_pet_breed($featured_pet_id);
        $featured_age = bpr_get_pet_age($featured_pet_id);
        $featured_gender = trim((string) get_post_meta($featured_pet_id, '_bpr_gender', true));
        $featured_size = trim((string) get_post_meta($featured_pet_id, '_bpr_size', true));
        $featured_excerpt = get_the_excerpt($featured_pet_id);
        if (!$featured_excerpt) {
            $featured_excerpt = wp_strip_all_tags(get_post_field('post_content', $featured_pet_id));
        }
        $featured_excerpt = wp_trim_words($featured_excerpt, 28);
        $featured_traits = bpr_get_home_featured_pet_traits($featured_pet_id, 3);
        $featured_statuses = get_the_terms($featured_pet_id, 'pet_status');
        $featured_details = array_filter(array($featured_age, $featured_gender, $featured_breed, $featured_size));
    ?>
        <section class="bpr-section bpr-home-featured-section">
            <div class="container bpr-content-wide">
                <div class="row align-items-center g-4 g-xl-5">
                    <div class="col-lg-4">
                        <div class="bpr-home-rescue-copy text-center text-lg-start">
                            <span class="bpr-pill mb-3"><i class="bi bi-heart-pulse-fill"></i> About the Rescue</span>
                            <h2 class="bpr-script-heading bpr-home-rescue-heading"><?php echo esc_html(get_theme_mod('bpr_home_intro_title', 'Bubbles Pet Rescue')); ?></h2>
                            <p><?php echo esc_html(get_theme_mod('bpr_home_intro_text', 'We’re a UAE community rescue helping dogs and cats move from uncertainty into safe foster care and loving permanent homes.')); ?></p>
                            <h3 class="bpr-script-subheading">Our Mission</h3>
                            <p class="mb-0"><?php echo esc_html(get_theme_mod('bpr_home_mission_text', 'Our mission is to rescue responsibly, support every pet’s individual needs, and find homes where they’ll be safe, understood, and loved for life.')); ?></p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <a class="bpr-home-featured-image-link" href="<?php echo esc_url($featured_url); ?>" aria-label="Meet <?php echo esc_attr($featured_name); ?>">
                            <?php if ($featured_image_id) : ?>
                                <?php echo wp_get_attachment_image($featured_image_id, 'large', false, array('class' => 'bpr-home-featured-image', 'loading' => 'eager', 'sizes' => '(max-width: 767px) calc(100vw - 2rem), (max-width: 991px) 50vw, 360px')); ?>
                            <?php else : ?>
                                <span class="bpr-home-featured-image bpr-home-featured-placeholder"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="bpr-home-featured-pet text-center text-md-start">
                            <?php if ($featured_statuses && !is_wp_error($featured_statuses)) : ?>
                                <div class="bpr-home-featured-statuses justify-content-center justify-content-md-start">
                                    <?php foreach ($featured_statuses as $status) : ?>
                                        <span><?php echo esc_html($status->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <h2 class="bpr-script-heading bpr-home-featured-name">Meet <?php echo esc_html($featured_name); ?>!</h2>
                            <?php if ($featured_excerpt) : ?><p class="bpr-home-featured-excerpt"><?php echo esc_html($featured_excerpt); ?></p><?php endif; ?>
                            <?php if ($featured_details) : ?><p class="bpr-home-featured-details"><?php echo esc_html(implode(' • ', $featured_details)); ?></p><?php endif; ?>
                            <?php if ($featured_traits) : ?>
                                <div class="bpr-home-featured-traits justify-content-center justify-content-md-start">
                                    <?php foreach ($featured_traits as $trait) : ?><span><?php echo esc_html($trait); ?></span><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a class="btn btn-bpr-primary bpr-home-featured-button" href="<?php echo esc_url($featured_url); ?>">Learn More About <?php echo esc_html($featured_name); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php else : ?>
        <section class="bpr-section bpr-home-featured-section">
            <div class="container">
                <div class="bpr-card p-4 text-center">Add a published dog or cat to show a featured pet on the homepage.</div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>

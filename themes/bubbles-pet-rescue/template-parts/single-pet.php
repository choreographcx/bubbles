<main class="bpr-single-profile">
    <div class="container bpr-content-wide">
        <?php while (have_posts()) : the_post();
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            $pet_label = $post_type === 'cat' ? 'Cat' : 'Dog';
            $archive_url = get_post_type_archive_link($post_type) ?: home_url($post_type === 'cat' ? '/cats/' : '/dogs/');
            $archive_label = $post_type === 'cat' ? 'Cats' : 'Dogs';
            $gallery_ids = function_exists('bpr_core_get_gallery_ids')
                ? bpr_core_get_gallery_ids($post_id, true)
                : array_filter(array((int) get_post_thumbnail_id($post_id)));
            $personality_tags = function_exists('bpr_core_get_personality_tags')
                ? bpr_core_get_personality_tags($post_id)
                : array();
            $detail_groups = function_exists('bpr_core_get_detail_groups')
                ? bpr_core_get_detail_groups($post_id)
                : array(
                    'Quick Details' => array_filter(array(
                        'Breed' => get_post_meta($post_id, '_bpr_breed', true),
                        'Age' => get_post_meta($post_id, '_bpr_age', true),
                        'Gender' => get_post_meta($post_id, '_bpr_gender', true),
                        'Size' => get_post_meta($post_id, '_bpr_size', true),
                        'Location' => get_post_meta($post_id, '_bpr_location', true),
                    )),
                    'Compatibility' => array_filter(array(
                        'Additional Notes' => get_post_meta($post_id, '_bpr_good_with', true),
                    )),
                );

            $quick_details = $detail_groups['Quick Details'] ?? array();
            $breed = $quick_details['Breed'] ?? bpr_get_pet_breed($post_id);
            $age = bpr_get_pet_age($post_id);
            $gender = $quick_details['Gender'] ?? trim((string) get_post_meta($post_id, '_bpr_gender', true));

            $application_link = get_post_meta($post_id, '_bpr_application_link', true) ?: home_url('/adoption-application/');
            $foster_link = get_post_meta($post_id, '_bpr_foster_link', true) ?: home_url('/foster-application/');
            $application_link = add_query_arg('pet', get_the_title($post_id), $application_link);
            $foster_link = add_query_arg('pet', get_the_title($post_id), $foster_link);
            $wishlist_link = get_theme_mod('bpr_amazon_wishlist_url', 'https://www.amazon.ae/hz/wishlist/ls/1TTLP5QW4EVHM');
            $health_notes = trim((string) get_post_meta($post_id, '_bpr_health', true));
            $compatibility_notes = trim((string) get_post_meta($post_id, '_bpr_good_with', true));
            $is_adopted = has_term('adopted', 'pet_status', $post_id);
            $carousel_id = 'bprPetCarousel' . $post_id;
            $modal_id = 'bprPetGalleryModal' . $post_id;
            $modal_carousel_id = 'bprPetModalCarousel' . $post_id;
            $terms = get_the_terms($post_id, 'pet_status');
            $display_terms = array();
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->slug !== 'available') {
                        $display_terms[] = $term;
                    }
                }
            }

            $sidebar_details = array();
            $sidebar_priority = array(
                'Size', 'Weight', 'Color', 'Coat', 'Location',
                'Spayed/Neutered', 'Vaccinations Current', 'Microchipped', 'Dewormed', 'Special Needs',
                'Good with Dogs', 'Good with Cats', 'Good with Children',
                'House Trained', 'Crate Trained', 'Leash Trained', 'Apartment Suitable', 'Can Be Left Alone', 'Energy Level',
            );

            foreach ($sidebar_priority as $detail_label) {
                foreach ($detail_groups as $group_fields) {
                    if (isset($group_fields[$detail_label]) && $group_fields[$detail_label] !== '') {
                        $sidebar_details[$detail_label] = $group_fields[$detail_label];
                        break;
                    }
                }
            }
        ?>
            <nav class="bpr-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                <span aria-hidden="true">&raquo;</span>
                <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($archive_label); ?></a>
                <span aria-hidden="true">&raquo;</span>
                <span><?php the_title(); ?></span>
            </nav>

            <article class="bpr-profile-layout">
                <div class="row g-4 g-xl-5 align-items-start">
                    <div class="col-lg-7 bpr-profile-copy-column">
                        <header class="bpr-profile-header">
                            <h1 class="bpr-script-heading bpr-profile-title">Meet <?php the_title(); ?></h1>
                            <?php if ($display_terms) : ?>
                                <div class="bpr-profile-statuses" aria-label="Pet status">
                                    <?php foreach ($display_terms as $term) : ?>
                                        <span><?php echo esc_html($term->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </header>

                        <div class="entry-content bpr-profile-story">
                            <?php the_content(); ?>
                        </div>


                        <div class="bpr-profile-actions">
                            <?php if (!$is_adopted) : ?>
                                <a class="btn btn-bpr-primary" href="<?php echo esc_url($application_link); ?>">Adopt <?php the_title(); ?></a>
                            <?php endif; ?>
                            <a class="btn btn-bpr-secondary" href="<?php echo esc_url($foster_link); ?>">Ask About Fostering</a>
                        </div>

                        <?php if ($wishlist_link) : ?>
                            <section class="bpr-profile-support" aria-labelledby="bpr-support-<?php echo esc_attr($post_id); ?>">
                                <h2 id="bpr-support-<?php echo esc_attr($post_id); ?>">Want to send some love this way?</h2>
                                <p>You can help with food, bedding, toys, and care supplies through the Bubbles Pets Rescue Amazon wishlist.</p>
                                <a href="<?php echo esc_url($wishlist_link); ?>" target="_blank" rel="noopener">Shop the Amazon wishlist</a>
                            </section>
                        <?php endif; ?>

                        <?php if ($personality_tags) : ?>
                            <section class="bpr-profile-personality" aria-labelledby="bpr-personality-<?php echo esc_attr($post_id); ?>">
                                <h2 id="bpr-personality-<?php echo esc_attr($post_id); ?>" class="visually-hidden">Personality</h2>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($personality_tags as $tag) : ?>
                                        <span class="bpr-personality-tag"><?php echo esc_html($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endif; ?>

                        <?php if ($compatibility_notes || $health_notes) : ?>
                            <div class="bpr-profile-notes">
                                <?php if ($compatibility_notes) : ?>
                                    <section class="bpr-profile-note">
                                        <h2>Home and Compatibility Notes</h2>
                                        <p><?php echo nl2br(esc_html($compatibility_notes)); ?></p>
                                    </section>
                                <?php endif; ?>
                                <?php if ($health_notes) : ?>
                                    <section class="bpr-profile-note">
                                        <h2>Health and Care Notes</h2>
                                        <p><?php echo nl2br(esc_html($health_notes)); ?></p>
                                    </section>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <aside class="col-lg-5 bpr-profile-media-column" aria-label="<?php echo esc_attr(get_the_title()); ?> photos and details">
                        <div class="bpr-profile-media-sticky">
                            <?php if ($gallery_ids) : ?>
                                <div id="<?php echo esc_attr($carousel_id); ?>" class="carousel slide bpr-profile-carousel" data-bs-touch="true" data-bs-interval="false">
                                    <?php if (count($gallery_ids) > 1) : ?>
                                        <div class="carousel-indicators">
                                            <?php foreach ($gallery_ids as $index => $image_id) : ?>
                                                <button type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide-to="<?php echo esc_attr($index); ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Photo <?php echo esc_attr($index + 1); ?>"></button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="carousel-inner">
                                        <?php foreach ($gallery_ids as $index => $image_id) : ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <button class="bpr-gallery-open" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo esc_attr($modal_id); ?>" data-bpr-slide="<?php echo esc_attr($index); ?>" aria-label="Open photo <?php echo esc_attr($index + 1); ?> of <?php echo esc_attr(get_the_title()); ?>">
                                                    <?php echo wp_get_attachment_image($image_id, 'large', false, array(
                                                        'class' => 'd-block w-100 bpr-profile-carousel-image',
                                                        'alt' => sprintf('%s — photo %d', get_the_title(), $index + 1),
                                                        'loading' => $index === 0 ? 'eager' : 'lazy',
                                                        'sizes' => '(max-width: 991px) calc(100vw - 2rem), 480px',
                                                    )); ?>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($gallery_ids) > 1) : ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous photo</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($carousel_id); ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next photo</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <div class="bpr-profile-carousel bpr-profile-placeholder d-flex align-items-center justify-content-center">
                                    <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>

                            <?php if ($age || $gender || $breed) : ?>
                                <p class="bpr-profile-summary"><?php echo esc_html(implode(' • ', array_filter(array($age, $gender, $breed)))); ?></p>
                            <?php endif; ?>

                            <?php if ($sidebar_details) : ?>
                                <section class="bpr-profile-facts" aria-labelledby="bpr-facts-<?php echo esc_attr($post_id); ?>">
                                    <h2 id="bpr-facts-<?php echo esc_attr($post_id); ?>" class="visually-hidden"><?php the_title(); ?> details</h2>
                                    <dl>
                                        <?php foreach ($sidebar_details as $label => $value) : ?>
                                            <div>
                                                <dt><span aria-hidden="true"></span><?php echo esc_html($label); ?>:</dt>
                                                <dd><?php echo esc_html($value); ?></dd>
                                            </div>
                                        <?php endforeach; ?>
                                    </dl>
                                </section>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </article>

            <?php if ($gallery_ids) : ?>
                <div class="modal fade bpr-gallery-modal" id="<?php echo esc_attr($modal_id); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h2 class="modal-title fs-5"><?php the_title(); ?> Photos</h2>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0 d-flex align-items-center">
                                <div id="<?php echo esc_attr($modal_carousel_id); ?>" class="carousel slide w-100" data-bs-touch="true" data-bs-interval="false">
                                    <div class="carousel-inner">
                                        <?php foreach ($gallery_ids as $index => $image_id) : ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <?php echo wp_get_attachment_image($image_id, 'full', false, array('class' => 'd-block bpr-modal-image', 'alt' => sprintf('%s — photo %d', get_the_title(), $index + 1), 'loading' => 'lazy')); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($gallery_ids) > 1) : ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($modal_carousel_id); ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($modal_carousel_id); ?>" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var modal = document.getElementById('<?php echo esc_js($modal_id); ?>');
                    if (!modal) return;
                    modal.addEventListener('show.bs.modal', function (event) {
                        var trigger = event.relatedTarget;
                        var slide = trigger ? parseInt(trigger.getAttribute('data-bpr-slide') || '0', 10) : 0;
                        var carouselElement = document.getElementById('<?php echo esc_js($modal_carousel_id); ?>');
                        if (carouselElement && window.bootstrap) {
                            bootstrap.Carousel.getOrCreateInstance(carouselElement, { interval: false }).to(slide);
                        }
                    });
                });
                </script>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>

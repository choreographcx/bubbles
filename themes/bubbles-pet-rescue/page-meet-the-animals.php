<?php
/*
 * Combined adoption directory: every dog and cat in one grid with species
 * filter chips. Applies automatically to the page with slug "meet-the-animals".
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$pets = get_posts(array(
    'post_type' => array('dog', 'cat'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => array(
        'menu_order' => 'ASC',
        'title' => 'ASC',
    ),
));

$pet_groups = array(
    'adoptable' => array(),
    'soon' => array(),
);
$species_counts = array('dog' => 0, 'cat' => 0);

foreach ($pets as $pet) {
    $status_slugs = wp_get_post_terms($pet->ID, 'pet_status', array('fields' => 'slugs'));
    $status_slugs = is_wp_error($status_slugs) ? array() : $status_slugs;

    if (in_array('adopted', $status_slugs, true)) {
        continue;
    }

    $is_soon = array_intersect($status_slugs, array('coming-soon', 'medical-care', 'adoption-pending'));
    $pet_groups[$is_soon ? 'soon' : 'adoptable'][] = $pet;
    $species_counts[get_post_type($pet->ID)]++;
}

$total = $species_counts['dog'] + $species_counts['cat'];
$show_filter = $species_counts['dog'] > 0 && $species_counts['cat'] > 0;
?>
<main id="bpr-main" class="bpr-section">
    <div class="container bpr-content-narrow">
        <nav class="bpr-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span aria-hidden="true">&raquo;</span>
            <span><?php the_title(); ?></span>
        </nav>

        <div class="text-center mb-4">
            <h1 class="display-5 bpr-heading"><?php the_title(); ?></h1>
            <?php while (have_posts()) : the_post(); ?>
                <?php if (trim(get_the_content())) : ?>
                    <div class="bpr-lede mx-auto" style="max-width: 44rem;"><?php the_content(); ?></div>
                <?php else : ?>
                    <p class="bpr-lede mx-auto" style="max-width: 44rem;"><?php esc_html_e('Every animal here is rescued, cared for by volunteers, and waiting for a foster or forever home in the UAE.', 'bubbles-pet-rescue'); ?></p>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>

        <?php if ($show_filter) : ?>
            <div class="bpr-species-filter d-flex flex-wrap gap-2 justify-content-center mb-4" id="bprSpeciesFilter" role="group" aria-label="<?php esc_attr_e('Filter by species', 'bubbles-pet-rescue'); ?>">
                <button type="button" class="bpr-filter-chip is-active" data-species-filter="all"><?php printf(esc_html__('All (%d)', 'bubbles-pet-rescue'), (int) $total); ?></button>
                <button type="button" class="bpr-filter-chip" data-species-filter="dog"><?php printf(esc_html__('Dogs (%d)', 'bubbles-pet-rescue'), (int) $species_counts['dog']); ?></button>
                <button type="button" class="bpr-filter-chip" data-species-filter="cat"><?php printf(esc_html__('Cats (%d)', 'bubbles-pet-rescue'), (int) $species_counts['cat']); ?></button>
            </div>
        <?php endif; ?>

        <?php if ($total === 0) : ?>
            <div class="bpr-card p-5 text-center">
                <p class="h5 bpr-section-title mb-2"><?php esc_html_e('No animals are listed right now.', 'bubbles-pet-rescue'); ?></p>
                <p class="mb-4"><?php esc_html_e('New arrivals appear on Instagram first. Follow us to see them early, or tell us you are open to adopting and we will get in touch.', 'bubbles-pet-rescue'); ?></p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a class="btn btn-bpr-primary" href="<?php echo esc_url(get_theme_mod('bpr_instagram_url') ?: 'https://www.instagram.com/bubbles.petsrescue/'); ?>"><?php esc_html_e('Follow on Instagram', 'bubbles-pet-rescue'); ?></a>
                    <a class="btn btn-bpr-secondary" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Register Your Interest', 'bubbles-pet-rescue'); ?></a>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $sections = array(
            'adoptable' => array('title' => __('Ready for Adoption', 'bubbles-pet-rescue')),
            'soon' => array('title' => __('Available Soon', 'bubbles-pet-rescue')),
        );

        foreach ($sections as $group_key => $section) :
            $group_pets = $pet_groups[$group_key];
            if (!$group_pets) {
                continue;
            }
        ?>
            <section class="bpr-dog-archive-section" data-pet-section aria-labelledby="bpr-<?php echo esc_attr($group_key); ?>-title">
                <h2 id="bpr-<?php echo esc_attr($group_key); ?>-title" class="bpr-script-heading text-center">
                    <?php echo esc_html($section['title']); ?>
                </h2>

                <div class="row g-4 justify-content-center bpr-dog-card-grid">
                    <?php foreach ($group_pets as $pet) : ?>
                        <div class="col-sm-6 col-lg-4 bpr-dog-card-column" data-species="<?php echo esc_attr(get_post_type($pet->ID)); ?>">
                            <?php bpr_pet_card($pet->ID, 'dog-archive'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if ($total > 0) : ?>
            <p class="bpr-empty-message text-center d-none" data-filter-empty><?php esc_html_e('No animals match this filter right now.', 'bubbles-pet-rescue'); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>

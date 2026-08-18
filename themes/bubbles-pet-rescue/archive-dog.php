<?php
get_header();

$dogs = get_posts(array(
    'post_type' => 'dog',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => array(
        'menu_order' => 'ASC',
        'title' => 'ASC',
    ),
));

$dog_groups = array(
    'adoptable' => array(),
    'soon' => array(),
);

foreach ($dogs as $dog) {
    $status_slugs = wp_get_post_terms($dog->ID, 'pet_status', array('fields' => 'slugs'));
    $status_slugs = is_wp_error($status_slugs) ? array() : $status_slugs;

    if (in_array('adopted', $status_slugs, true)) {
        continue;
    }

    $is_soon = array_intersect($status_slugs, array('coming-soon', 'medical-care', 'adoption-pending'));
    $dog_groups[$is_soon ? 'soon' : 'adoptable'][] = $dog;
}
?>
<main id="bpr-main" class="bpr-dog-archive">
    <div class="container bpr-content-narrow">
        <nav class="bpr-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span aria-hidden="true">&raquo;</span>
            <span>Dogs</span>
        </nav>

        <?php
        $sections = array(
            'adoptable' => array(
                'title' => 'Adoptable Dogs',
                'empty' => 'There aren’t any dogs ready for adoption right now. Please check back soon.',
            ),
            'soon' => array(
                'title' => 'Available Soon',
                'empty' => '',
            ),
        );

        foreach ($sections as $group_key => $section) :
            $group_dogs = $dog_groups[$group_key];
            if (!$group_dogs && $section['empty'] === '') {
                continue;
            }
        ?>
            <section class="bpr-dog-archive-section" aria-labelledby="bpr-<?php echo esc_attr($group_key); ?>-title">
                <h1 id="bpr-<?php echo esc_attr($group_key); ?>-title" class="bpr-script-heading text-center">
                    <?php echo esc_html($section['title']); ?>
                </h1>

                <?php if ($group_dogs) : ?>
                    <div class="row g-4 justify-content-center bpr-dog-card-grid">
                        <?php foreach ($group_dogs as $dog) : ?>
                            <div class="col-sm-6 col-lg-4 bpr-dog-card-column">
                                <?php bpr_pet_card($dog->ID, 'dog-archive'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="bpr-empty-message text-center"><?php echo esc_html($section['empty']); ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</main>
<?php get_footer(); ?>

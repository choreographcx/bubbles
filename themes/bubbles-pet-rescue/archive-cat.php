<?php get_header(); ?>
<main class="bpr-section">
    <div class="container">
        <div class="mb-5"><span class="bpr-pill mb-3">Adoptable Cats</span><h1 class="display-5 bpr-heading">Meet the Cats</h1><p class="bpr-lede">Every cat listed here is waiting for a calm foster home or a forever family.</p></div>
        <div class="row g-4">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?><div class="col-md-6 col-lg-4"><?php bpr_pet_card(); ?></div><?php endwhile; else : ?><div class="col-12"><div class="bpr-card p-5">No cats are listed right now.</div></div><?php endif; ?>
        </div>
        <div class="mt-5"><?php the_posts_pagination(); ?></div>
    </div>
</main>
<?php get_footer(); ?>

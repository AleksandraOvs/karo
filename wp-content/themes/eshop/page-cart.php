<?php get_header() ?>
<section class="page-content">
    <div class="fixed-container">
        <h1 class="page-title" data-scroll-animation="fade-down">
            <?= the_title() ?>
        </h1>
        <div class="page-content__cart">
            <?php the_content(); ?>
        </div>

    </div>

</section>

<?php //get_template_part('template-parts/section-contacts') 
?>


<?php get_footer() ?>
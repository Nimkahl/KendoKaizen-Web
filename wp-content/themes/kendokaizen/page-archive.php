<?php
/* Template Name: KendoKaizen Archive */
get_header('kendokaizen'); ?>
<main class="kk-main">
    <section class="kk-hero">
        <div><p class="kk-eyebrow">PAST EVENTS</p><h1>The Kendo <em>archive.</em></h1></div>
        <p class="kk-hero-copy">Explore completed events, newest first. Looking ahead? <a href="<?php echo esc_url(home_url('/')); ?>">Visit the upcoming calendar</a>.</p>
    </section>
    <?php echo do_shortcode('[kendokaizen_events scope="past" view="list"]'); ?>
</main>
<?php get_footer('kendokaizen'); ?>

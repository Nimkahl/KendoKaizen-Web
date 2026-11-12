<?php
/* Template Name: KendoKaizen About us */
get_header('kendokaizen'); ?>
<main class="kk-main kk-about-page">
<section class="kk-hero"><div><p class="kk-eyebrow">KendoKaizen</p><h1>About <em>us.</em></h1></div></section>
<div class="kk-about-grid">
    <section class="kk-event-disclaimer kk-creator-card" aria-labelledby="kk-creator-title">
        <h2 id="kk-creator-title">About the creator</h2>
        <p>KendoKaizen is an independent project created by a slightly forgetful kendoka who could never quite remember when European kendo events were taking place. It brings together seminars, competitions, gradings and more in one place, making it easier to plan your next event and connect with the kendo community.</p>
        <p class="kk-creator-motto"><strong>Plan. Practice. Enjoy.</strong></p>
        <img class="kk-creator-fox" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/creator-fox.png'); ?>" alt="Smiling fox mascot wearing kendo armour and holding a shinai" width="1280" height="1280">
    </section>
        <section class="kk-event-disclaimer" id="event-information" aria-labelledby="kk-event-disclaimer-title">
            <p class="kk-eyebrow">Please check with the organiser</p>
            <h2 id="kk-event-disclaimer-title">About event information</h2>
            <p>KendoKaizen is an independent community calendar. Unless a listing says otherwise, KendoKaizen is not the organiser or an official representative of the events featured here. Listings are shared for information and are compiled from organiser submissions and publicly available sources.</p>
            <p>Event organisers are responsible for their own events and decisions, including the programme, eligibility, fees, registration, venue, and any changes, postponements, cancellations or refunds. Event details may be incomplete or change after publication. Please use the official event link or contact the organiser to confirm the latest information before registering, booking travel or attending.</p>
            <p>If you notice an error or an outdated listing, <a href="<?php echo esc_url(kk_page_url('contact')); ?>">please contact KendoKaizen</a>.</p>
        </section>
</div>
</main>
<?php get_footer('kendokaizen'); ?>

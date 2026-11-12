<?php
/* Template Name: KendoKaizen How it works */
get_header('kendokaizen'); ?>
<main class="kk-main kk-how-page">
    <section class="kk-hero">
        <div><p class="kk-eyebrow">For event organisers</p><h1>Share your <em>Kendo event.</em></h1></div>
        <p class="kk-hero-copy">Competitions, seminars, gradings and training camps. Here’s how to get your event listed on KendoKaizen.</p>
    </section>
    <section class="kk-how-content" aria-label="How event submission works">
        <ol class="kk-how-steps">
            <li><h2>Send us the details</h2><p>Use the event-submission form on our Contact page. Include the event name, type, country, city, start date and your email. Add the end date for a multi-day event.</p></li>
            <li><h2>Add useful information</h2><p>If available, include the venue, organiser, fees, official website or registration link, and any practical information. You can also upload a poster in JPG, PNG or WebP format.</p></li>
            <li><h2>We review your submission</h2><p>Submissions are saved for review before publication. We check the details and may contact you if something needs clarification. Sending the form does not publish your event immediately.</p></li>
            <li><h2>Your event joins the calendar</h2><p>Once approved, your event appears on KendoKaizen. Visitors can read its details and follow your official registration link. Completed events move to the archive.</p></li>
        </ol>
        <div class="kk-how-note"><h2>Keep your listing up to date</h2><p>If dates, venue or other details change, contact us with the event name and the corrections. Please also let us know about postponements or cancellations.</p><p>KendoKaizen shares event information. Registration, payments and the running of the event remain with the organiser.</p></div>
        <div class="kk-how-actions"><a class="kk-organiser-contact" href="<?php echo esc_url(kk_page_url('contact') . '#submit-event'); ?>">Submit your event</a><a href="<?php echo esc_url(kk_page_url('contact')); ?>">Contact us</a></div>
    </section>
</main>
<?php get_footer('kendokaizen'); ?>

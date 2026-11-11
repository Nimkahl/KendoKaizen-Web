<?php
/* Template Name: KendoKaizen Contact */
get_header('kendokaizen'); ?>
<main class="kk-main kk-contact-page">
        <section class="kk-contact" id="contact" aria-labelledby="kk-contact-title">
            <div class="kk-contact-heading">
                <p class="kk-eyebrow">Contribute · Ask · Connect</p>
                <h1 id="kk-contact-title">Contact KendoKaizen</h1>
                <p>Have a question, suggestion or a Kendo event to share? Use the forms below.</p>
            </div>

            <?php if (isset($_GET['kk_status'])) : ?>
                <?php $kk_status = sanitize_key(wp_unslash($_GET['kk_status'])); ?>
                <?php if ($kk_status === 'message-sent') : ?>
                    <div class="kk-form-notice is-success" role="status">Thanks — your message has been sent.</div>
                <?php elseif ($kk_status === 'event-submitted') : ?>
                    <div class="kk-form-notice is-success" role="status">Thanks — your event has been submitted for review.</div>
                <?php elseif ($kk_status === 'error') : ?>
                    <div class="kk-form-notice is-error" role="alert">Something went wrong. Please check the required fields and try again.</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="kk-contact-grid">
                <article class="kk-contact-card">
                    <div class="kk-contact-card-copy">
                        <p class="kk-card-kicker">General contact</p>
                        <h3>Get in touch</h3>
                        <p>Have a question, suggestion or something you’d like to share about KendoKaizen? Send me a message.</p>
                    </div>
                    <form class="kk-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="kk_contact_message">
                        <?php wp_nonce_field('kk_contact_message', 'kk_contact_nonce'); ?>
                        <div class="kk-honeypot" aria-hidden="true">
                            <label>Website <input type="text" name="kk_website" tabindex="-1" autocomplete="off"></label>
                        </div>
                        <label>
                            <span>Name *</span>
                            <input type="text" name="kk_name" required autocomplete="name">
                        </label>
                        <label>
                            <span>Email *</span>
                            <input type="email" name="kk_email" required autocomplete="email">
                        </label>
                        <label>
                            <span>Message *</span>
                            <textarea name="kk_message" rows="6" required></textarea>
                        </label>
                        <?php $kk_privacy_url = get_privacy_policy_url() ?: home_url('/privacy-policy/'); ?>
                        <?php if ($kk_privacy_url) : ?>
                            <p class="kk-privacy-note">Privacy: the information you provide will only be used to respond to your request. <a href="<?php echo esc_url($kk_privacy_url); ?>">Read our Privacy Policy</a>.</p>
                        <?php endif; ?>
                        <button class="kk-submit" type="submit">Send message</button>
                    </form>
                </article>

                <article class="kk-contact-card kk-event-submit-card" id="submit-event">
                    <div class="kk-contact-card-copy">
                        <p class="kk-card-kicker">Community calendar</p>
                        <h3>Submit a Kendo event</h3>
                        <p>Know about a seminar, competition, grading or another Kendo event in Europe? Send the details and I’ll review it for inclusion in the calendar.</p>
                    </div>
                    <form class="kk-form kk-event-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="kk_submit_event">
                        <?php wp_nonce_field('kk_submit_event', 'kk_event_submit_nonce'); ?>
                        <div class="kk-honeypot" aria-hidden="true">
                            <label>Website <input type="text" name="kk_website" tabindex="-1" autocomplete="off"></label>
                        </div>
                        <label class="kk-field-wide">
                            <span>Event name *</span>
                            <input type="text" name="kk_event_name" required>
                        </label>
                        <label>
                            <span>Event type *</span>
                            <select name="kk_event_type" required>
                                <?php foreach (kk_event_type_options() as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Country *</span>
                            <input type="text" name="kk_country" required autocomplete="country-name">
                        </label>
                        <label>
                            <span>City *</span>
                            <input type="text" name="kk_city" required autocomplete="address-level2">
                        </label>
                        <label>
                            <span>Venue</span>
                            <input type="text" name="kk_venue">
                        </label>
                        <label>
                            <span>Start date *</span>
                            <input type="date" name="kk_start_date" required>
                        </label>
                        <label>
                            <span>End date</span>
                            <input type="date" name="kk_end_date">
                        </label>
                        <label>
                            <span>Organiser</span>
                            <input type="text" name="kk_organiser">
                        </label>
                        <label>
                            <span>Approximate price</span>
                            <input type="text" name="kk_price" placeholder="e.g. €50 or Free">
                        </label>
                        <label class="kk-field-wide">
                            <span>Official page / registration link</span>
                            <input type="url" name="kk_event_url" placeholder="https://…">
                        </label>
                        <label class="kk-field-wide">
                            <span>Your email *</span>
                            <input type="email" name="kk_submitter_email" required autocomplete="email">
                        </label>
                        <label class="kk-field-wide">
                            <span>Additional information</span>
                            <textarea name="kk_event_notes" rows="5"></textarea>
                        </label>
                        <label class="kk-field-wide">
                            <span>Poster / event image</span>
                            <input type="file" name="kk_event_poster" accept="image/jpeg,image/png,image/webp">
                            <small>Optional. JPG, PNG or WebP.</small>
                        </label>
                        <p class="kk-form-note kk-field-wide">Submitted events are reviewed before being published on KendoKaizen.</p>
                        <?php $kk_privacy_url = $kk_privacy_url ?? (get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>
                        <?php if ($kk_privacy_url) : ?>
                            <p class="kk-privacy-note kk-field-wide">Privacy: the information you provide will only be used to review and manage your event submission. <a href="<?php echo esc_url($kk_privacy_url); ?>">Read our Privacy Policy</a>.</p>
                        <?php endif; ?>
                        <button class="kk-submit kk-field-wide" type="submit">Submit event</button>
                    </form>
                </article>
            </div>
        </section>

</main>
<?php get_footer('kendokaizen'); ?>

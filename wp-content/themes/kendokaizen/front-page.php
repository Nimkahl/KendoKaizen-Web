<?php
/**
 * KendoKaizen front page.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <script>
        (function () {
            var choice = localStorage.getItem('kk-theme') || 'system';
            var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.kkTheme = choice === 'system' ? (dark ? 'dark' : 'light') : choice;
        }());
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="kk-shell">
    <header class="kk-topbar">
        <div class="kk-topbar-inner">
            <a class="kk-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="kk-brand-mark" aria-hidden="true">
                    <img class="kk-brand-image kk-brand-image-light" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kendokaizen-mark-black.png'); ?>" alt="">
                    <img class="kk-brand-image kk-brand-image-dark" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kendokaizen-mark-dark.png'); ?>" alt="">
                </span>
                <span class="kk-brand-name">KendoKaizen</span>
            </a>
            <div class="kk-topbar-actions">
                <button class="kk-mobile-menu-button" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="kk-primary-nav">
                    <span></span><span></span><span></span>
                </button>
                <nav class="kk-site-nav" id="kk-primary-nav" aria-label="Primary navigation">
                    <a href="#events">Events</a>
                    <a href="#kirikaeshi">Kirikaeshi</a>
                    <a href="#contact">Contact</a>
                </nav>
                <div class="kk-theme-switch" role="group" aria-label="Colour theme">
                    <button class="kk-theme-button" type="button" data-theme-choice="light" aria-label="Use light theme" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="5.5" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                    <button class="kk-theme-button" type="button" data-theme-choice="dark" aria-label="Use dark theme" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18.4 15.4A7.5 7.5 0 0 1 8.6 5.6 7.5 7.5 0 1 0 18.4 15.4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                    </button>
                    <button class="kk-theme-button" type="button" data-theme-choice="system" aria-label="Use system theme" aria-pressed="true">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="4.5" width="18" height="12" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8.5 20h7M12 16.5V20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="kk-main">
        <section class="kk-hero">
            <div>
                <p class="kk-eyebrow">PLAN - PRACTICE - ENJOY</p>
                <h1>European Kendo <em>events agenda.</em></h1>
            </div>
            <p class="kk-hero-copy">Seminars, competitions, gradings and more. Kendo all across Europe.</p>
        </section>

        <div id="events">
            <?php echo do_shortcode('[kendokaizen_events]'); ?>
        </div>

        <section class="kk-partner" id="kirikaeshi" aria-label="Kirikaeshi collaborator">
            <div class="kk-partner-intro">
                <p class="kk-eyebrow">Community collaborator</p>
                <h2>KendoKaizen x Kirikaeshi</h2>
            </div>

            <div class="kk-partner-card">
                <div class="kk-partner-top">
                    <div class="kk-partner-logo-box">
                        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-logo.png'); ?>" alt="Kirikaeshi">
                    </div>
                    <div class="kk-partner-copy">
                        <p>Kendo gear, accessories and original designs made for the Kendo community.</p>
                    </div>
                    <a class="kk-partner-cta" href="https://kirikaeshi.es/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">Visit website <span aria-hidden="true">↗</span></a>
                </div>

                <div class="kk-partner-gallery">
                    <div class="kk-partner-gallery-head">
                        <p class="kk-partner-gallery-label">Featured products</p>
                        <div class="kk-carousel-controls" aria-label="Kirikaeshi product carousel controls">
                            <button class="kk-carousel-button" type="button" data-carousel-prev aria-label="Previous products">←</button>
                            <button class="kk-carousel-button" type="button" data-carousel-next aria-label="Next products">→</button>
                        </div>
                    </div>
                    <div class="kk-partner-carousel" data-carousel>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/mugs-and-jars/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-mug.png'); ?>" alt="Kirikaeshi mugs">
                            <span>Mugs</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/clothing/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-shirt.png'); ?>" alt="Kirikaeshi t-shirts">
                            <span>T-shirts</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/kendo-bottles/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-bottle.png'); ?>" alt="Kirikaeshi kendo accessories">
                            <span>Kendo Accessories</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/tsuba-tsubadome/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-tsuba.png'); ?>" alt="Kirikaeshi Tsuba and Tsuba Dome">
                            <span>Tsuba and Tsuba Dome</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/tenugui/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-tenugui.png'); ?>" alt="Kirikaeshi tenugui">
                            <span>Tenugui</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/handbags-cases/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-accessories-bag.png'); ?>" alt="Kirikaeshi kendo accessories bag">
                            <span>Kendo Accessories Bag</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/categoria-producto/shinai-accessories/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-tsurus.png'); ?>" alt="Kirikaeshi shinai accessories">
                            <span>Shinai accessories</span>
                        </a>
                        <a class="kk-partner-slide" href="https://kirikaeshi.es/etiqueta-producto/kendogi/?v=12470fe406d4" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/kirikaeshi-navy-kendogi.png'); ?>" alt="Kirikaeshi keikogi or hakama">
                            <span>Keikogi and Hakama</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="kk-contact" id="contact" aria-labelledby="kk-contact-title">
            <div class="kk-contact-heading">
                <p class="kk-eyebrow">Contribute · Ask · Connect</p>
                <h2 id="kk-contact-title">Contact KendoKaizen</h2>
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

    <footer class="kk-footer">
        <div class="kk-footer-inner">
            <span>© <?php echo esc_html(wp_date('Y')); ?> KendoKaizen</span>
            <?php $kk_footer_privacy_url = get_privacy_policy_url() ?: home_url('/privacy-policy/'); ?>
            <?php if ($kk_footer_privacy_url) : ?>
                <a class="kk-footer-link" href="<?php echo esc_url($kk_footer_privacy_url); ?>">Privacy Policy</a>
            <?php endif; ?>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>

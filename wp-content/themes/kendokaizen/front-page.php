<?php get_header('kendokaizen'); ?>
    <main class="kk-main">
        <section class="kk-hero">
            <div>
                <p class="kk-eyebrow">PLAN - PRACTICE - ENJOY</p>
                <h1>European Kendo <em>events agenda.</em></h1>
            </div>
            <p class="kk-hero-copy">Seminars, competitions, gradings and more. Kendo all across Europe.</p>
        </section>



        <div class="kk-home-layout">
        <div id="events">
            <?php echo do_shortcode('[kendokaizen_events scope="upcoming" view="calendar"]'); ?>
        </div>

        <aside class="kk-home-sidebar" aria-label="Event map and organiser information">
            <section class="kk-map-card" aria-labelledby="kk-map-title">
                <header><h2 id="kk-map-title">Event map</h2><span id="kk-map-count" aria-live="polite"></span></header>
                <div id="kk-events-map" role="region" aria-label="Map of upcoming events in Europe"></div>
                <p id="kk-map-status" aria-live="polite">Loading event locations…</p>
                <details class="kk-map-locations"><summary>Browse locations</summary><p>City coordinates: <a href="https://www.geonames.org/">GeoNames</a>, via <a href="https://open-meteo.com/en/docs/geocoding-api">Open-Meteo</a>.</p><div id="kk-map-location-list"></div></details>
            </section>
            <section class="kk-organiser-card" aria-labelledby="kk-organiser-title">
                <p class="kk-organiser-eyebrow">For organisers</p>
                <h2 id="kk-organiser-title">Organising a competition?</h2>
                <p>Share your competition, seminar or grading with the Kendo community. Send us the details for review and publication on KendoKaizen.</p>
                <div class="kk-organiser-actions">
                    <a class="kk-organiser-contact" href="<?php echo esc_url(kk_page_url('contact')); ?>">Contact us</a>
                    <a class="kk-organiser-how" href="<?php echo esc_url(kk_page_url('how-it-works')); ?>">How it works</a>
                </div>
            </section>
        <section class="kk-sidebar-about" aria-labelledby="kk-sidebar-about-title">
            <h2 id="kk-sidebar-about-title">About</h2>

            <p><strong>Independent community calendar.</strong> KendoKaizen shares event information; each event is organised by its listed organiser. Details can change, so please confirm them with the organiser before making plans.</p>
            <a href="<?php echo esc_url(kk_page_url('about-us')); ?>">How event information works</a>
        </section>
        </aside>
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


    </main>

<?php get_footer('kendokaizen'); ?>

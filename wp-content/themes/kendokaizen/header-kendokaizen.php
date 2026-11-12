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
                    <a href="<?php echo esc_url(home_url('/')); ?>" <?php if (is_front_page()) echo 'aria-current="page"'; ?>>Home</a>
                    <a href="<?php echo esc_url(kk_page_url('archive')); ?>" <?php if (is_page_template('page-archive.php')) echo 'aria-current="page"'; ?>>Archive</a>
                    <a href="<?php echo esc_url(kk_page_url('contact')); ?>" <?php if (is_page_template('page-contact.php')) echo 'aria-current="page"'; ?>>Contact</a>
                    <a href="<?php echo esc_url(kk_page_url('about-us')); ?>" <?php if (is_page_template('page-about-us.php')) echo 'aria-current="page"'; ?>>About us</a>
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


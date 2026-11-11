    <footer class="kk-footer">
        <div class="kk-footer-inner">
            <span>© <?php echo esc_html(wp_date('Y')); ?> KendoKaizen</span>
            <?php $kk_footer_privacy_url = get_privacy_policy_url() ?: home_url('/privacy-policy/'); ?>
            <?php if ($kk_footer_privacy_url) : ?>
                <a class="kk-footer-link" href="<?php echo esc_url($kk_footer_privacy_url); ?>">Privacy Policy</a>
            <?php endif; ?>
            <a class="kk-footer-link" href="<?php echo esc_url(home_url('/#event-information')); ?>">Event information</a>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>

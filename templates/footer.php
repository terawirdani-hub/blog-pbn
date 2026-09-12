    </div>
    <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<footer class="site-footer">
    <?php if (setting('ad_footer_html') !== ''): ?>
        <div class="wrap ad-slot"><?= setting('ad_footer_html') ?></div>
    <?php endif; ?>
    <div class="wrap">
        <?php
        $footer = setting('footer_text');
        echo h($footer !== '' ? $footer : $siteName);
        ?>
    </div>
</footer>
</body>
</html>

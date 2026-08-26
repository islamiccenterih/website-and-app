<section class="page-hero">
    <div class="container">
        <?php
        $kicker = '404';
        $title = 'Page not found';
        $tag = 'h1';
        $lead = 'The address you opened is not part of this website.';
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p><a class="btn btn-gold" href="<?= e(url('/')) ?>">Return home</a></p>
    </div>
</section>

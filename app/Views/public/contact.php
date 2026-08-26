<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('contact', 'kicker', 'Get in touch');
        $title = page_copy('contact', 'title', 'Contact Us');
        $tag = 'h1';
        $lead = page_copy('contact', 'lead', 'Send a message to the administration. Address and phone details are managed from the Admin Panel.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section">
    <div class="container contact-grid">
        <div>
            <?php
            $kicker = page_copy('contact', 'form_kicker', 'Write to us');
            $title = page_copy('contact', 'form_title', 'Send a message');
            $tag = 'h2';
            $lead = '';
            $align = 'left';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
            <form class="form" method="post" action="<?= e(url('/contact-us')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="hp" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="field <?= isset($errors['name']) ? 'is-invalid' : '' ?>">
                    <label for="name"><?= e(tt('Name')) ?></label>
                    <input id="name" name="name" required maxlength="120" value="<?= e($old['name'] ?? '') ?>">
                    <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="field <?= isset($errors['email']) ? 'is-invalid' : '' ?>">
                    <label for="email"><?= e(tt('Email')) ?></label>
                    <input id="email" name="email" type="email" required value="<?= e($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="field <?= isset($errors['phone']) ? 'is-invalid' : '' ?>">
                    <label for="phone"><?= e(tt('Phone Number')) ?></label>
                    <input id="phone" name="phone" type="tel" value="<?= e($old['phone'] ?? '') ?>">
                    <?php if (isset($errors['phone'])): ?><div class="error"><?= e($errors['phone']) ?></div><?php endif; ?>
                </div>
                <div class="field <?= isset($errors['message']) ? 'is-invalid' : '' ?>">
                    <label for="message"><?= e(tt('Message')) ?></label>
                    <textarea id="message" name="message" rows="6" required><?= e($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?><div class="error"><?= e($errors['message']) ?></div><?php endif; ?>
                </div>
                <button class="btn btn-walnut" type="submit"><?= e(page_copy('contact', 'submit_label', 'Send message')) ?></button>
            </form>
        </div>
        <aside class="info-panel">
            <?php if (setting('contact_image')): ?>
                <img class="contact-photo" src="<?= e(upload_url((string) setting('contact_image'))) ?>" alt="">
            <?php endif; ?>
            <?php
            $kicker = page_copy('contact', 'aside_kicker', 'Islamic Center');
            $title = page_copy('contact', 'aside_title', 'Visit and write');
            $tag = 'h2';
            $lead = '';
            $align = 'left';
            $light = true;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <dl>
                <dt><?= e(tt('Address')) ?></dt>
                <dd><?= e((string) setting('contact_address', 'Address will be provided later.')) ?></dd>
                <dt><?= e(tt('Email')) ?></dt>
                <dd><a href="mailto:<?= e((string) setting('contact_email', '')) ?>"><?= e((string) setting('contact_email', 'info@example.com')) ?></a></dd>
                <dt><?= e(tt('Phone')) ?></dt>
                <dd><?= e((string) setting('contact_phone', '')) ?></dd>
                <dt><?= e(tt('Hours')) ?></dt>
                <dd><?= e((string) setting('contact_hours', '')) ?></dd>
            </dl>
        </aside>
    </div>
</section>

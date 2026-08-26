<section class="page-hero">
    <div class="container">
        <?php
        $kicker = ucfirst($course['mode']) . ' course';
        $title = $course['title'];
        $tag = 'h1';
        $lead = $course['short_description'];
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p class="hero-cta"><a class="btn btn-gold" href="#apply"><?= e(tt('Apply for this course')) ?></a></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php
        $kicker = page_copy('courses', 'detail_kicker', 'Course details');
        $title = $course['title'];
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="detail-hero">
            <img src="<?= e(upload_url($course['main_image'])) ?>" alt="<?= e($course['title']) ?>">
        </div>
        <div class="meta-row">
            <span class="pill">Fees: <?= e(money_display($course['fees'])) ?></span>
            <span class="pill">Duration: <?= e($course['duration'] ?: 'To be announced') ?></span>
            <span class="pill"><?= e(ucfirst($course['mode'])) ?></span>
        </div>
        <div class="prose">
            <?= ft($bodyHtml ?: '') ?: '<p>A full description will appear here once it is entered in the Admin Panel.</p>' ?>
            <?php if (!empty($course['additional_info'])): ?>
                <h2>Additional information</h2>
                <p><?= e(ft($course['additional_info'])) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($images): ?>
            <h2 style="margin-top:2rem"><?= e(page_copy('courses', 'images_heading', 'Course images')) ?></h2>
            <div class="gallery-grid" style="margin-top:1rem">
                <?php foreach ($images as $image): ?>
                    <a href="<?= e(upload_url($image['image_path'])) ?>" data-gallery-item data-alt="<?= e($image['caption'] ?: $course['title']) ?>">
                        <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['caption'] ?: $course['title']) ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $errors = $errors ?? [];
        $old = $old ?? [];
        ?>
        <div class="apply-block" id="apply">
            <?php
            $kicker = 'Enrolment';
            $title = tt('Apply for this course');
            $tag = 'h2';
            $lead = 'Leave your details for “' . $course['title'] . '”. The course is already selected from this page. The administration will contact you.';
            $align = 'left';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
            <form class="form stack-form" method="post" action="<?= e(url('/courses/' . $course['slug'])) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="hp" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="row-2">
                    <div class="field <?= isset($errors['name']) ? 'is-invalid' : '' ?>">
                        <label for="apply-name"><?= e(tt('Name')) ?></label>
                        <input id="apply-name" name="name" required maxlength="120" value="<?= e($old['name'] ?? '') ?>" autocomplete="name">
                        <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="field <?= isset($errors['email']) ? 'is-invalid' : '' ?>">
                        <label for="apply-email"><?= e(tt('Email')) ?></label>
                        <input id="apply-email" name="email" type="email" required maxlength="190" value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                        <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="row-2">
                    <div class="field <?= isset($errors['phone']) ? 'is-invalid' : '' ?>">
                        <label for="apply-phone"><?= e(tt('Phone Number')) ?></label>
                        <input id="apply-phone" name="phone" type="tel" required maxlength="40" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel">
                        <?php if (isset($errors['phone'])): ?><div class="error"><?= e($errors['phone']) ?></div><?php endif; ?>
                    </div>
                    <div class="field <?= isset($errors['whatsapp']) ? 'is-invalid' : '' ?>">
                        <label for="apply-whatsapp"><?= e(tt('WhatsApp number')) ?></label>
                        <input id="apply-whatsapp" name="whatsapp" type="tel" required maxlength="40" value="<?= e($old['whatsapp'] ?? '') ?>">
                        <?php if (isset($errors['whatsapp'])): ?><div class="error"><?= e($errors['whatsapp']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="field <?= isset($errors['address']) ? 'is-invalid' : '' ?>">
                    <label for="apply-address"><?= e(tt('Address')) ?></label>
                    <textarea id="apply-address" name="address" rows="4" required maxlength="400"><?= e($old['address'] ?? '') ?></textarea>
                    <?php if (isset($errors['address'])): ?><div class="error"><?= e($errors['address']) ?></div><?php endif; ?>
                </div>
                <button class="btn btn-walnut" type="submit"><?= e(tt('Submit enquiry')) ?></button>
            </form>
        </div>

        <p style="margin-top:2rem"><a class="btn btn-outline" href="<?= e(url('/courses')) ?>"><?= e(page_copy('courses', 'back_label', 'Back to courses')) ?></a></p>
    </div>
</section>
<div class="lightbox" data-lightbox><button class="lightbox-close" type="button" aria-label="Close">&times;</button><img src="" alt=""></div>

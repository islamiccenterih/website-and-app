<div class="auth-shell">
    <div class="auth-card">
        <img class="auth-logo" src="<?= e(asset('assets/img/logo.png')) ?>" alt="<?= e(site_name()) ?>">
        <p class="section-kicker"><?= e(tt('Administration')) ?></p>
        <h1><?= e(tt('Admin login')) ?></h1>
        <p><?= e(tt('Enter the email and password you were given. The owner sees the full panel. A manager or editor sees only the sections assigned to them.')) ?></p>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
        <form method="post" action="<?= e(url('/admin/login')) ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email"><?= e(tt('Email')) ?></label>
                <input id="email" name="email" type="email" required autocomplete="username">
            </div>
            <div class="field">
                <label for="password"><?= e(tt('Password')) ?></label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-walnut btn-block" type="submit"><?= e(tt('Sign in')) ?></button>
        </form>
        <p><a href="<?= e(url('/')) ?>"><?= e(tt('Back to website')) ?></a></p>
    </div>
</div>

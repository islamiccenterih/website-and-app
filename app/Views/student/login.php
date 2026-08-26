<div class="auth-shell">
    <div class="auth-card">
        <img class="auth-logo" src="<?= e(asset('assets/img/logo.png')) ?>" alt="<?= e(site_name()) ?>">
        <p class="section-kicker"><?= e(tt('Students')) ?></p>
        <h1><?= e(tt('Student login')) ?></h1>
        <p><?= e(tt('View your profile, courses, results, and join a live class for any course you are enrolled in.')) ?></p>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <form method="post" action="<?= e(url('/student/login')) ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email"><?= e(tt('Email')) ?></label>
                <input id="email" name="email" type="email" required autocomplete="username">
            </div>
            <div class="field">
                <label for="password"><?= e(tt('Password')) ?></label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-gold btn-block" type="submit"><?= e(tt('Sign in')) ?></button>
            <p class="help" style="margin-top:0.85rem"><?= e(tt('This browser stays signed in for 15 days. After that you will need to sign in again.')) ?></p>
        </form>
        <p class="help" style="margin-top:1rem">Demo: student@example.com / Student@12345</p>
        <p><a href="<?= e(url('/')) ?>"><?= e(tt('Back to website')) ?></a></p>
    </div>
</div>

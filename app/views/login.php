<?php
$alertMessage = null;
$error = $_GET['error'] ?? '';
if ($error === 'no_user') {
    $alertMessage = 'No account found with that username or email address.';
} elseif ($error === 'wrong_password') {
    $alertMessage = 'Incorrect password. Please verify and try again.';
} elseif ($error === 'invalid') {
    $alertMessage = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – Cinema</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/Auth.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-card__brand">
                <div class="auth-card__logo" aria-hidden="true">CA</div>
                <span class="auth-card__brand-text">Cinema Ticketing</span>
            </div>

            <h1 class="auth-card__title">Sign in</h1>
            <p class="auth-card__subtitle">Explore the academic demo with a shared account below. Changes may be visible to other visitors; please avoid real personal information and reused passwords.</p>

            <?php if ($alertMessage): ?>
                <div class="auth-card__alert auth-card__alert--error" role="alert">
                    <?php echo htmlspecialchars((string) $alertMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="/Cinema/loginUser" method="POST">
                <?php echo csrf_field(); ?>

                <div class="auth-card__group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" placeholder="name@example.com" autocomplete="username" required autofocus>
                </div>

                <div class="auth-card__group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <button class="auth-card__button" type="submit">Sign In &rarr;</button>

                <div class="demo-accounts">
                    <div class="demo-accounts__header">
                        <span class="demo-accounts__kicker">Quick Demo Accounts</span>
                        <span style="font-size:0.75rem; color:#78716c;">Click to fill</span>
                    </div>
                    <div class="demo-accounts__grid">
                        <div class="demo-account-pill">
                            <div class="demo-account-pill__info">
                                <span class="demo-account-pill__role role-admin">&#x1F6E1;&#xFE0F; Administrator</span>
                                <span class="demo-account-pill__name">Super Admin (<code>admin</code>)</span>
                            </div>
                            <button type="button" class="demo-account-pill__btn" onclick="fillDemo('admin', 'admin123')">Use</button>
                        </div>
                        <div class="demo-account-pill">
                            <div class="demo-account-pill__info">
                                <span class="demo-account-pill__role role-user">&#x1F464; Customer</span>
                                <span class="demo-account-pill__name">John Doe (<code>john_doe</code>)</span>
                            </div>
                            <button type="button" class="demo-account-pill__btn" onclick="fillDemo('john_doe', 'user123')">Use</button>
                        </div>
                    </div>
                </div>

                <div class="auth-card__footer">
                    <span>Don't have an account yet?</span>
                    <a href="/Cinema/register">Create Account</a>
                </div>
            </form>
            <p class="auth-card__footer"><a href="/Cinema/privacy">Privacy and cookies</a></p>
        </div>
    </main>

    <script>
        function fillDemo(username, password) {
            var userField = document.getElementById('username');
            var passField = document.getElementById('password');
            if (userField && passField) {
                userField.value = username;
                passField.value = password;
                userField.dispatchEvent(new Event('input', { bubbles: true }));
                passField.dispatchEvent(new Event('input', { bubbles: true }));
                passField.focus();
            }
        }
    </script>
</body>
</html>

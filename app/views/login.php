<?php
$alertMessage = null;
$error = $_GET['error'] ?? '';
if ($error === 'invalid') {
    $alertMessage = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login – Cinema</title>
        <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
        <link rel="stylesheet" href="/Cinema/public/css/Auth.css">

    </head>
    <body>
        <div class="auth-container">
            <div class="auth-card">
                <h2 class="auth-card__title">Sign in</h2>
                <form action="/Cinema/loginUser" method="POST">
                    <?php if ($alertMessage): ?>
                        <div class="auth-card__alert auth-card__alert--error" role="alert">
                            <?php echo htmlspecialchars((string) $alertMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="auth-card__group">
                        <label for="username">Username or email:</label>
                        <input type="text" id="username" name="username" placeholder="you@example.com" autocomplete="username" required>
                    </div>
                    <div class="auth-card__group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    </div>
                    <button class="auth-card__button" type="submit">Sign in</button>
                    <a href="register">register</a>
                </form>
            </div>
        </div>
    </body>
</html>
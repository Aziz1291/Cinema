<?php
$alertMessage = null;
$error = $_GET['error'] ?? '';
if ($error === 'validation') {
    $alertMessage = 'Please check your details. Username and email must be unique, passwords must match, and you must be 13 or older.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Cinema</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/Auth.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-card" style="width:min(100%, 520px);">
            <div class="auth-card__brand">
                <div class="auth-card__logo" aria-hidden="true">CA</div>
                <span class="auth-card__brand-text">Cinema Ticketing</span>
            </div>

            <h1 class="auth-card__title">Create Account</h1>
            <p class="auth-card__subtitle">Join to reserve seats, review showtimes, and access your tickets.</p>

            <?php if ($alertMessage): ?>
                <div class="auth-card__alert auth-card__alert--error" role="alert">
                    <?php echo htmlspecialchars((string) $alertMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="/Cinema/registerUser" method="POST">
                <?php echo csrf_field(); ?>

                <div class="auth-card__row">
                    <div class="auth-card__group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Jane" autocomplete="given-name" required>
                    </div>
                    <div class="auth-card__group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" autocomplete="family-name" required>
                    </div>
                </div>

                <div class="auth-card__group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" autocomplete="username" required>
                </div>

                <div class="auth-card__group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                </div>

                <div class="auth-card__row">
                    <div class="auth-card__group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create password" autocomplete="new-password" required>
                    </div>
                    <div class="auth-card__group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="password-strength password-strength--hidden" id="password-strength" aria-live="polite">
                    <div class="password-strength__bar-wrap">
                        <div class="password-strength__bar" id="password-strength-bar"></div>
                    </div>
                    <p class="password-strength__text" id="password-strength-text">Password strength: Weak</p>
                </div>

                <div class="auth-card__group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="date" id="birth_date" name="birth_date" autocomplete="bday" required>
                </div>

                <button class="auth-card__button" type="submit">Complete Registration &rarr;</button>

                <div class="auth-card__footer">
                    <span>Already have an account?</span>
                    <a href="/Cinema/login">Sign in</a>
                </div>
            </form>
        </div>
    </main>

    <script>
    (function () {
        const passwordInput = document.getElementById("password");
        const strengthWrapper = document.getElementById("password-strength");
        const strengthBar = document.getElementById("password-strength-bar");
        const strengthText = document.getElementById("password-strength-text");

        function evaluateStrength(password) {
            let score = 0;
            if (password.length >= 8) score += 1;
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score += 1;
            if (/\d/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;

            if (password.length === 0 || score <= 1) return "weak";
            if (score <= 3) return "medium";
            return "strong";
        }

        passwordInput.addEventListener("input", function () {
            if (passwordInput.value.length === 0) {
                strengthWrapper.classList.add("password-strength--hidden");
                strengthBar.className = "password-strength__bar";
                strengthText.textContent = "Password strength: Weak";
                return;
            }

            strengthWrapper.classList.remove("password-strength--hidden");
            const strength = evaluateStrength(passwordInput.value);

            strengthBar.className = "password-strength__bar password-strength__bar--" + strength;
            strengthText.textContent = "Password strength: " + strength.charAt(0).toUpperCase() + strength.slice(1);
        });
    })();
    </script>
</body>
</html>

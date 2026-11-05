<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
require_once __DIR__ . '/../../models/FilmModel.php';
$film = (new FilmModel())->getFilmById((int)($_GET['id'] ?? 0));
if (!$film) { header('Location: /Cinema/films'); exit; }
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
$currentPoster = $film->getPosterImage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Film – Cinema Administration</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>

<main class="page" id="main-content" tabindex="-1">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Catalogue Administration</p>
            <h2 class="page__title">Edit Film &middot; <?php echo htmlspecialchars((string)$film->getTitle(), ENT_QUOTES, 'UTF-8'); ?></h2>
            <div style="margin-top:6px;">
                <a href="/Cinema/films" class="back-link">&larr; Back to Films</a>
            </div>
        </div>
    </header>

    <div class="form-card">
        <form action="/Cinema/films/update" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo $film->getId(); ?>">
            <input type="hidden" name="old_poster" value="<?php echo htmlspecialchars((string)$currentPoster, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label class="form-label" for="film_title">Film Title <span class="muted">(max 100 characters)</span></label>
                <input type="text" id="film_title" name="title" class="form-input" maxlength="100" value="<?php echo htmlspecialchars((string)$film->getTitle(), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="film_duration">Runtime Duration <span class="muted">(in minutes)</span></label>
                <input type="number" id="film_duration" name="duration" class="form-input" min="1" max="480" value="<?php echo (int)$film->getDuration(); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="film_desc">Synopsis / Description <span class="muted">(max 255 characters)</span></label>
                <textarea id="film_desc" name="description" class="form-textarea" maxlength="255" rows="4" required><?php echo htmlspecialchars((string)$film->getDescription(), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="poster_file">Poster Image</label>
                <?php if ($currentPoster): ?>
                    <div style="display:flex; align-items:center; gap:16px; margin:8px 0; padding:12px; background:var(--tint); border-radius:var(--radius-md);">
                        <img src="/Cinema/public/assets/<?php echo htmlspecialchars((string)$currentPoster, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="Current poster" style="width:54px; height:80px; object-fit:cover; border-radius:4px; border:1px solid var(--line);">
                        <div>
                            <span style="font-size:0.85rem; font-weight:600; color:var(--ink); display:block;">Active Poster File</span>
                            <span class="muted" style="font-size:0.8rem;"><?php echo htmlspecialchars((string)$currentPoster, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" id="poster_file" name="poster_image" class="form-input" accept="image/jpeg,image/png,image/webp,image/gif">
                <span class="form-hint">Leave blank to keep current poster image.</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Update Film Details</button>
                <a href="/Cinema/films" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

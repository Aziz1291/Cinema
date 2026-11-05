<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Film – Cinema Administration</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>

<main class="page">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Catalogue Administration</p>
            <h2 class="page__title">Add New Film</h2>
            <div style="margin-top:6px;">
                <a href="/Cinema/films" class="back-link">&larr; Back to Films</a>
            </div>
        </div>
    </header>

    <div class="form-card">
        <form action="/Cinema/films/create" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label" for="film_title">Film Title <span class="muted">(max 100 characters)</span></label>
                <input type="text" id="film_title" name="title" class="form-input" maxlength="100" placeholder="e.g. Dune: Part Two" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="film_duration">Runtime Duration <span class="muted">(in minutes)</span></label>
                <input type="number" id="film_duration" name="duration" class="form-input" min="1" max="480" placeholder="120" required>
                <span class="form-hint">Used for automatic scheduling end-time and 15-minute gap calculation.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="film_desc">Synopsis / Description <span class="muted">(max 255 characters)</span></label>
                <textarea id="film_desc" name="description" class="form-textarea" maxlength="255" rows="4" placeholder="Brief summary of the film..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="poster_file">Theatrical Poster Image <span class="muted">(JPG, PNG, or WEBP, max 5 MB)</span></label>
                <input type="file" id="poster_file" name="poster_image" class="form-input" accept="image/jpeg,image/png,image/webp,image/gif">
                <span class="form-hint">2:3 theatrical portrait aspect ratio recommended.</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Save &amp; Add Film</button>
                <a href="/Cinema/films" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

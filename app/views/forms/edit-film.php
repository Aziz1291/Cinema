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
    <title>Edit Film – Admin</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Admin / Films</p>
        <h2 class="page__title">Edit Film</h2>
        <a href="/Cinema/films" class="back-link" style="margin-top:6px;">← Back to Films</a>
    </header>
    <section class="page-card" style="max-width:560px;">
        <form action="/Cinema/films/update" method="POST" enctype="multipart/form-data" style="display:grid;gap:14px;">
            <input type="hidden" name="id" value="<?php echo $film->getId(); ?>">
            <input type="hidden" name="old_poster" value="<?php echo htmlspecialchars((string)$currentPoster, ENT_QUOTES, 'UTF-8'); ?>">

            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Title <small>(max 100 chars)</small></label>
                <input type="text" name="title" maxlength="100" required
                       value="<?php echo htmlspecialchars((string)$film->getTitle(), ENT_QUOTES, 'UTF-8'); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Duration (minutes)</label>
                <input type="number" name="duration" min="1" required
                       value="<?php echo (int)$film->getDuration(); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Description <small>(max 255 chars)</small></label>
                <textarea name="description" maxlength="255" required rows="3"
                          style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;resize:vertical;"><?php echo htmlspecialchars((string)$film->getDescription(), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Poster Image</label>
                <?php if ($currentPoster): ?>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                        <img src="/Cinema/public/assets/<?php echo htmlspecialchars((string)$currentPoster, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="Current poster" style="width:60px;height:90px;object-fit:cover;border-radius:4px;border:1px solid rgba(255,255,255,.1);">
                        <span style="font-size:.8rem;color:#64748b;">Current poster</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="poster_image" accept="image/jpeg,image/png,image/webp,image/gif"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
                <small style="color:#64748b;">Leave empty to keep current poster.</small>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <a href="/Cinema/films" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

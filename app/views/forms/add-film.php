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
    <title>Add Film – Admin</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Admin / Films</p>
        <h2 class="page__title">Add New Film</h2>
        <a href="/Cinema/films" class="back-link" style="margin-top:6px;">← Back to Films</a>
    </header>
    <section class="page-card" style="max-width:560px;">
        <form action="/Cinema/films/create" method="POST" enctype="multipart/form-data" style="display:grid;gap:14px;">
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Title <small>(max 100 chars)</small></label>
                <input type="text" name="title" maxlength="100" required
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Duration (minutes)</label>
                <input type="number" name="duration" min="1" required
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Description <small>(max 255 chars)</small></label>
                <textarea name="description" maxlength="255" required rows="3"
                          style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;resize:vertical;"></textarea>
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Poster Image <small>(JPG / PNG / WEBP, max 5 MB)</small></label>
                <input type="file" name="poster_image" accept="image/jpeg,image/png,image/webp,image/gif"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
                <small style="color:#64748b;">Leave empty for no poster.</small>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary">Add Film</button>
                <a href="/Cinema/films" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

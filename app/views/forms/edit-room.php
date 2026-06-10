<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
require_once __DIR__ . '/../../models/RoomModel.php';
$room = (new RoomModel())->getRoomById((int)($_GET['id'] ?? 0));
if (!$room) { header('Location: /Cinema/rooms'); exit; }
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room – Admin</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Admin / Rooms</p>
        <h2 class="page__title">Edit Room</h2>
        <a href="/Cinema/rooms" class="back-link" style="margin-top:6px;">← Back to Rooms</a>
    </header>
    <section class="page-card" style="max-width:500px;">
        <form action="/Cinema/rooms/update" method="POST" style="display:grid;gap:14px;">
            <input type="hidden" name="id" value="<?php echo $room->getId(); ?>">
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Room Name <small>(max 50 chars)</small></label>
                <input type="text" name="name" maxlength="50" required
                       value="<?php echo htmlspecialchars((string)$room->getName(), ENT_QUOTES, 'UTF-8'); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Number of Rows</label>
                <input type="number" name="rowsNumber" min="1" required
                       value="<?php echo (int)$room->getRowsNumber(); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Total Seats</label>
                <input type="number" name="seatsNumber" min="1" required
                       value="<?php echo (int)$room->getSeatsNumber(); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <a href="/Cinema/rooms" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUser = null;
$isAdmin = false;
if (isset($_SESSION['id'])) {
    require_once __DIR__ . '/../models/UserModel.php';
    $userModel = new UserModel();
    $currentUser = $userModel->getUserById($_SESSION['id']);
    $isAdmin = $userModel->isAdmin($_SESSION['id']);
}
require_once __DIR__ . '/../models/RoomModel.php';
$rooms   = (new RoomModel())->getAllRooms();
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms – Cinema</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <p class="page__eyebrow">Venue</p>
            <h2 class="page__title">Rooms</h2>
            <p class="page__subtitle"><?php echo $isAdmin ? 'Admin view – add, edit or delete rooms.' : 'Browse our cinema rooms.'; ?></p>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/rooms/add" class="btn btn--primary" style="align-self:center;">+ Add Room</a>
        <?php endif; ?>
    </header>

    <?php if ($success === 'created'): ?><div class="alert alert--success">Room added successfully.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">Room updated successfully.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">Room deleted.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">Something went wrong. Please try again.</div><?php endif; ?>

    <?php if (empty($rooms)): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">No rooms yet</h3>
            <p class="page-card__text">Add rooms to the database to see them here.</p>
        </div>
    <?php else: ?>
        <section class="film-grid">
            <?php foreach ($rooms as $room): ?>
            <article class="film-card">
                <div class="film-card__poster" style="font-size:2.5rem;background:#1a2030;color:#334155;">🎬</div>
                <div class="film-card__body">
                    <h3 class="film-card__title"><?php echo htmlspecialchars((string)$room->getName(), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="film-card__meta"><?php echo (int)$room->getRowsNumber(); ?> rows · <?php echo (int)$room->getSeatsNumber(); ?> total seats</p>
                    <a href="/Cinema/seats?roomid=<?php echo $room->getId(); ?>" class="btn btn--secondary" style="margin-top:10px;">View Seats</a>

                    <?php if ($isAdmin): ?>
                        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                            <a href="/Cinema/rooms/edit?id=<?php echo $room->getId(); ?>" class="btn btn--secondary" style="flex:1;text-align:center;padding:7px 10px;font-size:.8rem;">Edit</a>
                            <form action="/Cinema/rooms/delete" method="POST" style="flex:1;margin:0;display:flex;" onsubmit="return confirm('Delete this room?');">
                                <input type="hidden" name="id" value="<?php echo $room->getId(); ?>">
                                <button type="submit" class="btn btn--primary" style="flex:1;padding:7px 10px;font-size:.8rem;background:#dc2626;">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

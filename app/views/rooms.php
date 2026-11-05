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
    <title>Auditoriums &amp; Rooms – Cinema</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Venue &amp; Facilities</p>
            <h2 class="page__title">Auditorium Rooms</h2>
            <p class="page__subtitle"><?php echo $isAdmin ? 'Admin Console &middot; Configure auditorium capacities, rows, and seating arrangements.' : 'Browse our screening halls and auditorium capacities.'; ?></p>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/rooms/add" class="btn btn--primary">+ Add Room</a>
        <?php endif; ?>
    </header>

    <?php if ($success === 'created'): ?><div class="alert alert--success">✓ Auditorium room created successfully with initialized seating.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">✓ Auditorium configuration updated.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">✓ Auditorium room deleted.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">⚠ Database transaction error. Please try again.</div><?php endif; ?>

    <?php if (empty($rooms)): ?>
        <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
            <span style="font-size:2.5rem; margin-bottom:8px; display:block;" aria-hidden="true">🏛️</span>
            <h3 style="margin-bottom:8px;">No auditoriums registered</h3>
            <p class="muted" style="margin-bottom:20px;">There are currently no cinema rooms configured in the venue database.</p>
            <?php if ($isAdmin): ?>
                <a href="/Cinema/rooms/add" class="btn btn--primary" style="margin:0 auto;">+ Create First Room</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <section class="film-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));" aria-label="Auditorium List">
            <?php foreach ($rooms as $room):
                $rows = (int)$room->getRowsNumber();
                $totalSeats = (int)$room->getSeatsNumber();
                $seatsPerRow = $rows > 0 ? (int)ceil($totalSeats / $rows) : 0;
            ?>
            <article class="room-card">
                <div class="room-card__header">
                    <div>
                        <span class="kicker" style="font-size:0.75rem;">Auditorium Hall</span>
                        <h3 style="font-size:1.3rem; margin-top:2px;"><?php echo htmlspecialchars((string)$room->getName(), ENT_QUOTES, 'UTF-8'); ?></h3>
                    </div>
                    <span class="film-card__tag tone-olive" style="font-size:0.75rem;">
                        <?php echo $totalSeats; ?> Seats
                    </span>
                </div>

                <div class="muted" style="font-size:0.9rem;">
                    <span>📐 <strong><?php echo $rows; ?></strong> rows &times; ~<strong><?php echo $seatsPerRow; ?></strong> seats per row</span>
                </div>

                <!-- Mini Seating Matrix Architectural Preview -->
                <div class="room-card__matrix-preview" aria-hidden="true" title="Physical seating layout representation">
                    <?php 
                    $previewRows = min(6, $rows);
                    $previewCols = min(12, $seatsPerRow);
                    for ($r = 0; $r < $previewRows; $r++): ?>
                        <div style="display:flex; gap:4px;">
                            <?php for ($c = 0; $c < $previewCols; $c++): ?>
                                <span class="matrix-dot"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                    <span style="color:#64748b; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.1em; margin-top:4px;">Screening Field</span>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:auto; padding-top:12px; border-top:1px solid var(--line);">
                    <a href="/Cinema/seats?roomid=<?php echo $room->getId(); ?>" class="btn btn--secondary btn--sm" style="flex:1;">
                        View Seating Chart
                    </a>
                    <?php if ($isAdmin): ?>
                        <a href="/Cinema/rooms/edit?id=<?php echo $room->getId(); ?>" class="btn btn--outline btn--sm">Edit</a>
                        <form action="/Cinema/rooms/delete" method="POST" style="margin:0;" onsubmit="return confirm('Delete \'<?php echo htmlspecialchars(addslashes($room->getName()), ENT_QUOTES, 'UTF-8'); ?>\'? All associated sessions and seats will be removed.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $room->getId(); ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                        </form>
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

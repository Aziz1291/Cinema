<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
$userModel   = new UserModel();
$currentUser = $userModel->getUserById($_SESSION['id']);
$isAdmin     = $userModel->isAdmin($_SESSION['id']);

if ($isAdmin) { header('Location: /Cinema/dashboard'); exit; }
require_once __DIR__ . '/../../models/ReservationModel.php';
$reservations = (new ReservationModel())->getReservationsByUserId($_SESSION['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations – Cinema</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Tickets</p>
        <h2 class="page__title">My Reservations</h2>
        <p class="page__subtitle">All your booked seats in one place.</p>
    </header>
    <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
        <div class="alert alert--success">✓ Reservation confirmed! Enjoy the show.</div>
    <?php endif; ?>
    <?php if (empty($reservations)): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">No reservations yet</h3>
            <p class="page-card__text">You haven't booked any tickets. <a href="/Cinema/films" style="color:#e50914;">Browse movies →</a></p>
        </div>
    <?php else: ?>
        <section class="film-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
            <?php foreach ($reservations as $res): ?>
            <article class="ticket">
                <h3 class="ticket__film"><?php echo htmlspecialchars((string)$res->getFilmTitle(), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="ticket__meta">🎬 <?php echo htmlspecialchars((string)$res->getRoomName(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="ticket__meta">🕐 <?php echo date('M j, Y – H:i', strtotime((string)$res->getStartTime())); ?></p>
                <div class="ticket__seat">
                    <div class="ticket__seat-label">Seat</div>
                    <div class="ticket__seat-num"><?php echo htmlspecialchars((string)$res->getRowLetter() . $res->getSeatNumber(), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

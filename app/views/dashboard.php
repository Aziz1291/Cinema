<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUser = null;
$isAdmin = false;
$stats = [];
if (isset($_SESSION['id'])) {
    require_once __DIR__ . '/../models/UserModel.php';
    require_once __DIR__ . '/../models/FilmModel.php';
    require_once __DIR__ . '/../models/RoomModel.php';
    require_once __DIR__ . '/../models/ReservationModel.php';
    
    $userModel = new UserModel();
    $currentUser = $userModel->getUserById($_SESSION['id']);
    $isAdmin = $userModel->isAdmin($_SESSION['id']);
    
    if ($isAdmin) {
        $stats['films'] = count((new FilmModel())->getAllFilms());
        $stats['rooms'] = count((new RoomModel())->getAllRooms());
        $stats['users'] = count($userModel->getAllUsers());
    } else {
        $stats['films'] = count((new FilmModel())->getAllFilms());
        $reservations = (new ReservationModel())->getReservationsByUserId($_SESSION['id']);
        $stats['seats'] = count($reservations);
        
        $nearest = null;
        $now = time();
        foreach ($reservations as $res) {
            $st = strtotime((string)$res->getStartTime());
            if ($st >= $now) {
                if ($nearest === null || $st < $nearest) {
                    $nearest = $st;
                }
            }
        }
        $stats['nearest'] = $nearest ? date('M j, H:i', $nearest) : 'None';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Cinema</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: #181818; border: none; border-radius: 4px; padding: 24px; display: flex; flex-direction: column; gap: 4px; box-shadow: none; }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: #ffffff; line-height: 1; }
        .stat-label { font-size: 0.9rem; color: #b3b3b3; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; }
        .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .nav-card { padding: 24px; border-radius: 4px; background: #181818; border: none; }
        .nav-card__title { font-size: 1.1rem; margin: 0; color: #ffffff; }
        .nav-card__text { font-size: 0.9rem; margin-top: 6px; color: #b3b3b3; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Dashboard</p>
        <h2 class="page__title"><?php echo $isAdmin ? 'Admin Overview' : 'Welcome back'; ?></h2>
        <p class="page__subtitle">
            <?php echo $isAdmin ? 'Manage films, rooms and sessions.' : 'Browse films, book seats, and track your reservations.'; ?>
        </p>
    </header>

    <?php if ($isAdmin): ?>
        <section class="stat-grid">
            <div class="stat-card"><span class="stat-value"><?php echo $stats['films']; ?></span><span class="stat-label">Total Films</span></div>
            <div class="stat-card"><span class="stat-value"><?php echo $stats['rooms']; ?></span><span class="stat-label">Total Rooms</span></div>
            <div class="stat-card"><span class="stat-value"><?php echo $stats['users']; ?></span><span class="stat-label">Total Users</span></div>
        </section>
        
        <section class="nav-grid">
            <a class="page-card nav-card" href="/Cinema/films">
                <h3 class="page-card__title nav-card__title">🎥 Manage Films</h3>
                <p class="page-card__text nav-card__text">Add, edit or remove movies.</p>
            </a>
            <a class="page-card nav-card" href="/Cinema/rooms">
                <h3 class="page-card__title nav-card__title">🏛️ Manage Rooms</h3>
                <p class="page-card__text nav-card__text">Review seat configurations.</p>
            </a>
            <a class="page-card nav-card" href="/Cinema/schedules">
                <h3 class="page-card__title nav-card__title">📅 Manage Sessions</h3>
                <p class="page-card__text nav-card__text">Create movie schedules.</p>
            </a>
        </section>
    <?php else: ?>
        <section class="stat-grid">
            <div class="stat-card"><span class="stat-value"><?php echo $stats['films']; ?></span><span class="stat-label">Available Films</span></div>
            <div class="stat-card"><span class="stat-value"><?php echo $stats['seats']; ?></span><span class="stat-label">Seats Booked</span></div>
            <div class="stat-card"><span class="stat-value" style="font-size: 1.4rem; margin-top: 6px;"><?php echo $stats['nearest']; ?></span><span class="stat-label">Next Movie</span></div>
        </section>
        
        <section class="nav-grid">
            <a class="page-card nav-card" href="/Cinema/films">
                <h3 class="page-card__title nav-card__title">🎬 Now Playing</h3>
                <p class="page-card__text nav-card__text">See available movies.</p>
            </a>
            <a class="page-card nav-card" href="/Cinema/schedules">
                <h3 class="page-card__title nav-card__title">📅 Sessions</h3>
                <p class="page-card__text nav-card__text">Browse upcoming sessions.</p>
            </a>
            <a class="page-card nav-card" href="/Cinema/reservations">
                <h3 class="page-card__title nav-card__title">🎟️ My Tickets</h3>
                <p class="page-card__text nav-card__text">Manage booked tickets.</p>
            </a>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

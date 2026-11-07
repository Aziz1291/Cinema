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
    require_once __DIR__ . '/../models/ScheduleModel.php';
    
    $userModel = new UserModel();
    $currentUser = $userModel->getUserById($_SESSION['id']);
    $isAdmin = $userModel->isAdmin($_SESSION['id']);
    
    if ($isAdmin) {
        $stats['films'] = count((new FilmModel())->getAllFilms());
        $stats['rooms'] = count((new RoomModel())->getAllRooms());
        $stats['users'] = count($userModel->getAllUsers());
        $stats['schedules'] = count((new ScheduleModel())->getAllSchedules());
        $stats['reservations'] = count((new ReservationModel())->getAllReservations());
    } else {
        $stats['films'] = count((new FilmModel())->getAllFilms('', true));
        $reservations = (new ReservationModel())->getReservationsByUserId($_SESSION['id']);
        
        $nearest = null;
        $nearestRes = null;
        $now = time();
        $upcomingCount = 0;
        foreach ($reservations as $res) {
            $st = strtotime((string)$res->getStartTime());
            if ($st >= $now) {
                $upcomingCount++;
                if ($nearest === null || $st < $nearest) {
                    $nearest = $st;
                    $nearestRes = $res;
                }
            }
        }
        $stats['seats'] = $upcomingCount;
        $stats['nearest'] = $nearest ? (date('D, M j', $nearest) . ' · ' . date('H:i', $nearest)) : 'None scheduled';
        $stats['nearestRes'] = $nearestRes;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Cinema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
    <link rel="icon" href="/Cinema/public/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/Cinema/public/favicon.svg">
    <link rel="icon" type="image/png" sizes="48x48" href="/Cinema/public/favicon-48.png">
    <link rel="apple-touch-icon" href="/Cinema/public/apple-touch-icon.png">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page" id="main-content" tabindex="-1">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Overview</p>
            <h2 class="page__title"><?php echo $isAdmin ? 'Management Console' : 'Welcome back, ' . htmlspecialchars((string)($currentUser ? $currentUser->getFirstName() : 'Guest'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="page__subtitle">
                <?php echo $isAdmin ? 'Real-time overview of the cinematic catalog, screening schedules, and auditorium rooms.' : 'Browse the latest films, book reserved seating, and access your digital ticket stubs.'; ?>
            </p>
        </div>
        <div style="font-size:0.9rem; font-weight:600; color:var(--muted); background:var(--surface); border:1px solid var(--line); border-radius:var(--radius-full); padding:6px 16px;">
             <?php echo date('l, F j, Y'); ?>
        </div>
    </header>

    <?php if ($isAdmin): ?>
        <!-- Admin Metrics -->
        <section class="stat-grid" aria-label="Admin Key Statistics">
            <div class="stat-card">
                <span class="stat-card__label">Active Catalogue</span>
                <span class="stat-card__value"><?php echo $stats['films']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Feature films currently registered</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Cinema Auditoriums</span>
                <span class="stat-card__value"><?php echo $stats['rooms']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Configured screening halls</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Upcoming Sessions</span>
                <span class="stat-card__value"><?php echo $stats['schedules']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Screenings not yet finished</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Customer Bookings</span>
                <span class="stat-card__value"><?php echo $stats['reservations']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Total confirmed reservations</p>
            </div>
        </section>


    <?php else: ?>
        <!-- Customer Metrics -->
        <section class="stat-grid" aria-label="Customer Statistics">
            <div class="stat-card">
                <span class="stat-card__label">Now Playing</span>
                <span class="stat-card__value"><?php echo $stats['films']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Films currently on screen</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Upcoming Tickets</span>
                <span class="stat-card__value"><?php echo $stats['seats']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Seats booked for screenings still to come</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Next Screening</span>
                <span class="stat-card__value" style="font-size:1.5rem; margin-top:8px; line-height:1.2;"><?php echo $stats['nearest']; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Upcoming movie date &amp; time</p>
            </div>
        </section>

        <?php if (!empty($stats['nearestRes'])):
            $nr = $stats['nearestRes'];
        ?>
        <!-- Customer Spotlight: Upcoming Screening -->
        <section class="spotlight-card">
            <div>
                <span class="kicker" style="color:var(--tone-sea-fg); font-size:0.75rem;">Your Next Theatrical Experience</span>
                <h3 class="spotlight-card__title" style="margin:4px 0;"><?php echo htmlspecialchars((string)$nr->getFilmTitle(), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="spotlight-card__meta">
                     <?php echo htmlspecialchars((string)$nr->getRoomName(), ENT_QUOTES, 'UTF-8'); ?> &middot;
                    Seat <strong><?php echo htmlspecialchars((string)$nr->getRowLetter() . $nr->getSeatNumber(), ENT_QUOTES, 'UTF-8'); ?></strong> &middot;
                    Starts <?php echo date('D, M j', strtotime((string)$nr->getStartTime())); ?> &middot; <?php echo date('H:i', strtotime((string)$nr->getStartTime())); ?>
                </p>
            </div>
            <a href="/Cinema/reservations" class="btn btn--primary">View Digital Ticket &rarr;</a>
        </section>
        <?php endif; ?>

    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

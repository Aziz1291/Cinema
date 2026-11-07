<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ReservationModel.php';

$userModel   = new UserModel();
$currentUser = $userModel->getUserById($_SESSION['id']);
$isAdmin     = $userModel->isAdmin($_SESSION['id']);

$search = trim($_GET['search'] ?? '');
$reservationModel = new ReservationModel();

if ($isAdmin) {
    $reservations = $reservationModel->getAllReservations($search);
    // The tiles always describe every booking, whatever the search box says.
    $everyReservation = $search === '' ? $reservations : $reservationModel->getAllReservations();
    $totalCount = count($everyReservation);
    $activeCount = 0;
    $totalRevenue = 0.0;
    $now = time();
    foreach ($everyReservation as $r) {
        $st = strtotime((string)$r->getStartTime());
        if ($st >= $now) {
            $activeCount++;
        }
        $totalRevenue += (float)$r->getTicketPrice();
    }
} else {
    $reservations = $reservationModel->getReservationsByUserId($_SESSION['id']);
    // Soonest screening first for upcoming tickets, most recent first for past ones.
    $isFuture = fn($r) => strtotime((string)$r->getStartTime()) >= time();
    $upcoming = array_reverse(array_values(array_filter($reservations, $isFuture)));
    $past = array_values(array_filter($reservations, fn($r) => !$isFuture($r)));
}
$flashSuccess = flash_take('success');
$flashError = flash_take('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isAdmin ? 'Customer Reservations & Bookings – Cinema Administration' : 'My Reservations – Cinema'; ?></title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>

<main class="page" id="main-content" tabindex="-1">
    <?php if ($flashSuccess === 'booked'): ?>
        <div class="alert alert--success"><strong>✓ Reservation Confirmed!</strong> Your seats have been held and your digital ticket stubs are ready below.</div>
    <?php elseif ($flashSuccess === 'cancelled'): ?>
        <div class="alert alert--success">✓ Reservation cancelled. The seat is free again.</div>
    <?php endif; ?>
    <?php if ($flashError === 'cancel'): ?>
        <div class="alert alert--error">⚠ That reservation cannot be cancelled. Only upcoming bookings can be cancelled.</div>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <!-- ── ADMIN CUSTOMER RESERVATIONS VIEW ───────────────────── -->
        <header class="page__header">
            <div>
                <p class="kicker page__eyebrow">Ticketing &amp; Box Office</p>
                <h2 class="page__title">Customer Reservations</h2>
                <p class="page__subtitle">Admin Console &middot; Overview of customer ticket reservations, occupancy, and box office admissions.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <?php if (!empty($reservations)): ?>
                    <button type="button" class="btn btn--secondary" onclick="window.print()">
                         Print Guest List
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <!-- Admin Key Metrics -->
        <section class="stat-grid" aria-label="Reservation Key Metrics" style="margin-bottom:28px;">
            <div class="stat-card">
                <span class="stat-card__label">Total Confirmed Tickets</span>
                <span class="stat-card__value"><?php echo $totalCount; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Across all registered customer accounts</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Upcoming Bookings</span>
                <span class="stat-card__value"><?php echo $activeCount; ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Tickets for screenings that have not started</p>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Box Office Receipts</span>
                <span class="stat-card__value" style="color:var(--cinema-amber);">$<?php echo number_format($totalRevenue, 2); ?></span>
                <p class="muted" style="font-size:0.85rem; margin-top:4px;">Estimated admissions value</p>
            </div>
        </section>

        <!-- Search Bar -->
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px;">
            <form method="GET" action="/Cinema/reservations" style="display:flex; gap:10px; width:min(100%, 460px);">
                <input type="text" name="search" class="form-input" placeholder="Search by customer, film, or room..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn--secondary">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="/Cinema/reservations" class="btn btn--outline" title="Clear filter">Clear</a>
                <?php endif; ?>
            </form>
            <?php if (!empty($search)): ?>
                <p class="muted" style="font-size:0.9rem;">Filtering by: <strong>&ldquo;<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>&rdquo;</strong> (<?php echo count($reservations); ?> found)</p>
            <?php endif; ?>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
                
                <h3 style="margin-bottom:8px;">No reservations found</h3>
                <p class="muted" style="margin-bottom:20px;">
                    <?php echo !empty($search) ? 'No bookings match your search query. Try another term.' : 'There are currently no customer reservations in the database.'; ?>
                </p>
                <a href="/Cinema/schedules" class="btn btn--secondary" style="margin:0 auto;">View Screening Sessions</a>
            </div>
        <?php else: ?>
            <!-- Desktop / Tablet Table View -->
            <div class="res-wrap" style="background:var(--surface); border:1px solid var(--line); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-subtle);">
                <div style="overflow-x:auto;">
                    <table class="res-table" style="width:100%; border-collapse:collapse; text-align:left; font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--tint); border-bottom:1px solid var(--line); color:var(--muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em;">
                                <th style="padding:14px 18px;">Stub ID</th>
                                <th style="padding:14px 18px;">Customer</th>
                                <th style="padding:14px 18px;">Film &amp; Showtime</th>
                                <th style="padding:14px 18px;">Auditorium &amp; Seat</th>
                                <th style="padding:14px 18px;">Status</th>
                                <th style="padding:14px 18px; text-align:right;">Price</th>
                                <th style="padding:14px 18px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res):
                                $resId = (int)$res->getId();
                                $filmTitle = (string)$res->getFilmTitle();
                                $poster = $res->getPosterImage();
                                $roomName = (string)$res->getRoomName();
                                $startTime = strtotime((string)$res->getStartTime());
                                $isUpcoming = $startTime >= time();
                                $seatLabel = (string)$res->getRowLetter() . $res->getSeatNumber();
                                $custName = (string)($res->getCustomerName() ?: 'Guest Customer');
                                $custEmail = (string)$res->getUserEmail();
                                $price = (float)$res->getTicketPrice();
                                $initial = strtoupper(substr($custName, 0, 1));
                            ?>
                            <tr style="border-bottom:1px solid var(--line); transition:background 0.15s ease;">
                                <td data-label="Stub ID" style="padding:16px 18px; font-family:var(--font-mono); font-weight:700; color:var(--ink);">
                                    #CNM-<?php echo str_pad((string)$resId, 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td data-label="Customer" style="padding:16px 18px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width:34px; height:34px; border-radius:var(--radius-full); background:var(--ink); color:#fff; display:grid; place-items:center; font-weight:700; font-size:0.85rem; flex-shrink:0;">
                                            <?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:600; color:var(--ink); line-height:1.2;"><?php echo htmlspecialchars($custName, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="muted" style="font-size:0.8rem;"><?php echo htmlspecialchars($custEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Film &amp; Showtime" style="padding:16px 18px;">
                                    <div style="font-weight:600; color:var(--ink);"><?php echo htmlspecialchars($filmTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="muted" style="font-size:0.82rem; margin-top:2px;">
                                         <?php echo date('D, M j, Y', $startTime) . ' &middot; ' . date('H:i', $startTime); ?>
                                    </div>
                                </td>
                                <td data-label="Auditorium &amp; Seat" style="padding:16px 18px;">
                                    <div style="font-weight:500;"> <?php echo htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <span class="film-card__tag" style="background:var(--tint); color:var(--ink); font-weight:700; font-family:var(--font-mono); margin-top:4px; display:inline-block;">
                                        Row <?php echo htmlspecialchars((string)$res->getRowLetter(), ENT_QUOTES, 'UTF-8'); ?> &middot; Seat <?php echo (int)$res->getSeatNumber(); ?>
                                    </span>
                                </td>
                                <td data-label="Status" style="padding:16px 18px;">
                                    <?php if ($isUpcoming): ?>
                                        <span class="film-card__tag tone-olive">
                                            &#9679; Confirmed Upcoming
                                        </span>
                                    <?php else: ?>
                                        <span class="film-card__tag" style="background:var(--tint); color:var(--muted);">
                                            Past Screening
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Price" style="padding:16px 18px; text-align:right; font-weight:700; color:var(--ink); font-family:var(--font-mono);">
                                    $<?php echo number_format($price, 2); ?>
                                </td>
                                <td data-label="Actions" style="padding:16px 18px; text-align:right;">
                        <form method="post" action="/Cinema/reservations/cancel" style="margin:0;" onsubmit="return confirm('Cancel this reservation? The seat will be released.');">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $resId ?>">
                            <button type="submit" class="btn btn--outline btn--sm">Cancel</button>
                        </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ── CUSTOMER PERSONAL TICKETS VIEW ─────────────────────── -->
        <header class="page__header">
            <div>
                <p class="kicker page__eyebrow">Digital Ticket Stubs</p>
                <h2 class="page__title">My Reservations</h2>
                <p class="page__subtitle">Present these verified ticket stubs upon entry at the auditorium door.</p>
            </div>
            <?php if (!empty($reservations)): ?>
                <button type="button" class="btn btn--secondary" onclick="window.print()">
                     Print Tickets
                </button>
            <?php endif; ?>
        </header>


        <?php if (empty($reservations)): ?>
            <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
                
                <h3 style="margin-bottom:8px;">No reservations yet</h3>
                <p class="muted" style="margin-bottom:20px;">You haven't reserved any cinema tickets yet. Explore what's playing today!</p>
                <a href="/Cinema/films" class="btn btn--primary" style="margin:0 auto;">Browse Now Playing &rarr;</a>
            </div>
        <?php else: ?>
            <?php foreach ([['Upcoming', $upcoming], ['Past', $past]] as [$sectionTitle, $sectionItems]): ?>
            <h3 style="margin:24px 0 12px;"><?php echo $sectionTitle; ?> <span class="muted" style="font-weight:500;">(<?php echo count($sectionItems); ?>)</span></h3>
            <?php if (!$sectionItems): ?>
                <p class="muted"><?php echo $sectionTitle === 'Upcoming' ? 'No upcoming screenings booked.' : 'No past screenings yet.'; ?></p>
            <?php else: ?>
            <section class="film-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));" aria-label="<?php echo $sectionTitle; ?> tickets">
                <?php foreach ($sectionItems as $res):
                    $filmTitle = (string)$res->getFilmTitle();
                    $roomName = (string)$res->getRoomName();
                    $startTime = strtotime((string)$res->getStartTime());
                    $seatFull = (string)$res->getRowLetter() . $res->getSeatNumber();
                    $resId = (int)$res->getId();
                ?>
                <article class="ticket"<?php if ($sectionTitle === 'Past') echo ' style="opacity:.65"'; ?> aria-label="Ticket for <?php echo htmlspecialchars($filmTitle, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="ticket__header">
                        <span class="ticket__brand">Cinema Ticketing</span>
                        <span class="ticket__admit-tag">Admit One</span>
                    </div>

                    <div>
                        <h3 class="ticket__film"><?php echo htmlspecialchars($filmTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="ticket__meta" style="margin-top:4px;">
                             <?php echo htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="ticket__meta" style="color:var(--ink); font-weight:600; margin-top:2px;">
                             <?php echo date('D, M j, Y', $startTime) . ' &middot; ' . date('H:i', $startTime); ?>
                        </p>
                    </div>

                    <!-- Perforated Cutout Tear Divider -->
                    <div class="ticket__divider" aria-hidden="true"></div>

                    <div class="ticket__seat-box">
                        <div>
                            <span class="ticket__seat-label">Assigned Seat</span>
                            <div class="ticket__seat-num"><?php echo htmlspecialchars($seatFull, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span class="muted" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">Stub ID</span>
                            <div style="font-family:var(--font-mono); font-size:0.85rem; font-weight:700; color:var(--ink);">#CNM-<?php echo str_pad((string)$resId, 5, '0', STR_PAD_LEFT); ?></div>
                        </div>
                    </div>

                    <div class="ticket__barcode" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                    <?php if ($sectionTitle === 'Upcoming'): ?>
                        <form method="post" action="/Cinema/reservations/cancel" style="margin:0;" onsubmit="return confirm('Cancel this reservation? The seat will be released.');">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $resId ?>">
                            <button type="submit" class="btn btn--outline btn--sm">Cancel</button>
                        </form>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

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
require_once __DIR__ . '/../models/ScheduleModel.php';
require_once __DIR__ . '/../models/FilmModel.php';

$film_id       = isset($_GET['film_id']) ? (int)$_GET['film_id'] : 0;
$scheduleModel = new ScheduleModel();
$schedules     = $film_id > 0 ? $scheduleModel->getSchedulesByFilmId($film_id) : $scheduleModel->getAllSchedules();
$film          = $film_id > 0 ? (new FilmModel())->getFilmById($film_id) : null;

$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

// Extract dates for schedule day-strips
$datesMap = [];
foreach ($schedules as $s) {
    $d = date('Y-m-d', strtotime((string)$s->getStartTime()));
    $datesMap[$d] = ($datesMap[$d] ?? 0) + 1;
}
ksort($datesMap);
$selectedDate = $_GET['date'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions &amp; Showtimes – Cinema</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page" id="main-content" tabindex="-1">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Screening Programme</p>
            <h2 class="page__title">
                <?php echo $film ? 'Sessions &middot; ' . htmlspecialchars((string)$film->getTitle(), ENT_QUOTES, 'UTF-8') : 'All Screenings'; ?>
            </h2>
            <p class="page__subtitle">
                <?php echo $isAdmin ? 'Admin Console &middot; Schedule showtimes, check auditorium room turnaround buffers, and manage pricing.' : 'Select a screening time below to view the hall layout and reserve your seats.'; ?>
            </p>
            <?php if ($film): ?>
                <div style="margin-top:8px;">
                    <a href="/Cinema/films" class="back-link">&larr; Back to all films</a>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/schedules/add<?php echo $film_id > 0 ? '?film_id=' . $film_id : ''; ?>" class="btn btn--primary">+ Add Session</a>
        <?php endif; ?>
    </header>

    <?php if ($success === 'created'): ?><div class="alert alert--success">✓ Screening session successfully scheduled.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">✓ Screening session updated.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">✓ Screening session deleted.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">⚠ Database transaction error. Please try again.</div><?php endif; ?>
    <?php if ($error === 'invalid'):   ?><?= invalid_alert() ?><?php endif; ?>
    <?php if ($error === 'time'):      ?><div class="alert alert--error">⚠ Invalid timing: End time must fall after start time.</div><?php endif; ?>
    <?php if ($error === 'overlap'):   ?>
        <div class="alert alert--warning" role="alert">
            <strong>Room Turnaround Collision:</strong> This session conflicts with an existing screening in the same auditorium. Our policy requires a <strong>15-minute cleaning buffer</strong> between shows.
        </div>
    <?php endif; ?>

    <?php if (empty($schedules)): ?>
        <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
            
            <h3 style="margin-bottom:8px;">No sessions scheduled</h3>
            <p class="muted" style="margin-bottom:20px;">
                <?php echo $film ? 'There are currently no upcoming showtimes for this film.' : 'No screenings have been programmed yet across our auditoriums.'; ?>
            </p>
            <?php if ($isAdmin): ?>
                <a href="/Cinema/schedules/add<?php echo $film_id > 0 ? '?film_id=' . $film_id : ''; ?>" class="btn btn--primary" style="margin:0 auto;">+ Create First Session</a>
            <?php else: ?>
                <a href="/Cinema/films" class="btn btn--secondary" style="margin:0 auto;">Browse Other Movies</a>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <!-- Schedule Day-Strips Navigation -->
        <nav class="schedule-day-strip" aria-label="Showtime dates day-strip">
            <button type="button" class="day-strip-btn <?php echo $selectedDate === 'all' ? 'is-active' : ''; ?>" data-date="all" onclick="filterByDate('all', this)">
                <span class="day-strip-btn__day">All Dates</span>
                <span class="day-strip-btn__count"><?php echo count($schedules); ?> shows</span>
            </button>
            <?php foreach ($datesMap as $d => $count): 
                $timestamp = strtotime($d);
                $isToday = (date('Y-m-d') === $d);
                $dayLabel = $isToday ? 'Today' : date('D', $timestamp);
                $dateLabel = date('M j', $timestamp);
                $isActive = ($selectedDate === $d);
            ?>
            <button type="button" class="day-strip-btn <?php echo $isActive ? 'is-active' : ''; ?>" data-date="<?php echo $d; ?>" onclick="filterByDate('<?php echo $d; ?>', this)">
                <span class="day-strip-btn__day"><?php echo $dayLabel; ?></span>
                <span class="day-strip-btn__date"><?php echo $dateLabel; ?> &middot; <?php echo $count; ?></span>
            </button>
            <?php endforeach; ?>
        </nav>

        <section class="film-grid" id="schedulesGrid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));" aria-label="Available Sessions">
            <?php foreach ($schedules as $s):
                $filmTitle = (string)$s->getFilmTitle();
                $roomName = (string)$s->getRoomName();
                $startTime = strtotime((string)$s->getStartTime());
                $endTime = strtotime((string)$s->getEndTime());
                $price = (float)$s->getTicketPrice();
                $schedDate = date('Y-m-d', $startTime);
            ?>
            <article class="schedule-card" data-date="<?php echo $schedDate; ?>">
                <div>
                    <span class="kicker" style="font-size:0.75rem;"> <?php echo htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3 class="schedule-card__film" style="margin-top:4px;"><?php echo htmlspecialchars($filmTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                </div>

                <div class="schedule-card__meta-row">
                    <span style="font-weight:600; color:var(--ink);">
                         <?php echo date('D, M j', $startTime) . ' · ' . date('H:i', $startTime); ?>
                    </span>
                    <span class="muted" style="font-size:0.85rem;">
                        (Ends ~<?php echo date('H:i', $endTime); ?>)
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-top:4px; padding-top:12px; border-top:1px solid var(--line);">
                    <div>
                        <span class="muted" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em; display:block;">Admission</span>
                        <span class="schedule-card__price">$<?php echo number_format($price, 2); ?></span>
                    </div>

                    <?php if ($isAdmin): ?>
                        <div style="display:flex; gap:8px;">
                            <a href="/Cinema/schedules/edit?id=<?php echo $s->getId(); ?>" class="btn btn--secondary btn--sm">Edit</a>
                            <form action="/Cinema/schedules/delete" method="POST" style="margin:0;" onsubmit="return confirm('Delete this screening session?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $s->getId(); ?>">
                                <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <a href="/Cinema/seats?schedule_id=<?php echo $s->getId(); ?>" class="btn btn--primary btn--sm">
                            Select Seats &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

        <!-- No sessions notice for selected day -->
        <div id="noDaySessionsNotice" class="stat-card" style="display:none; text-align:center; padding:48px 20px; margin-top:16px;">
            <h3>No Screenings on Selected Day</h3>
            <p class="muted">There are no showtimes programmed for this specific date.</p>
        </div>
    <?php endif; ?>
</main>

<script>
function filterByDate(date, btn) {
    document.querySelectorAll('.day-strip-btn').forEach(b => b.classList.remove('is-active'));
    if (btn) btn.classList.add('is-active');
    
    const cards = document.querySelectorAll('.schedule-card');
    let visible = 0;
    cards.forEach(card => {
        if (date === 'all' || card.getAttribute('data-date') === date) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    const emptyNotice = document.getElementById('noDaySessionsNotice');
    if (emptyNotice) {
        emptyNotice.style.display = (visible === 0) ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const dateParam = params.get('date');
    if (dateParam && dateParam !== 'all') {
        const targetBtn = document.querySelector(`.day-strip-btn[data-date="${dateParam}"]`);
        if (targetBtn) {
            filterByDate(dateParam, targetBtn);
        }
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

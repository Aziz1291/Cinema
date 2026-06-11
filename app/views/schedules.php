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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions – Cinema</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <p class="page__eyebrow">Sessions</p>
            <h2 class="page__title">
                <?php echo $film ? 'Sessions for ' . htmlspecialchars((string)$film->getTitle(), ENT_QUOTES, 'UTF-8') : 'All Sessions'; ?>
            </h2>
            <p class="page__subtitle"><?php echo $isAdmin ? 'Admin view – add, edit or delete sessions.' : 'Select a session time to book your seats.'; ?></p>
            <a href="/Cinema/films" class="back-link" style="margin-top:8px;">← Back to Films</a>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/schedules/add" class="btn btn--primary" style="align-self:center;">+ Add Session</a>
        <?php endif; ?>
    </header>

    <?php if ($success === 'created'): ?><div class="alert alert--success">Session created successfully.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">Session updated successfully.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">Session deleted.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">Something went wrong. Please try again.</div><?php endif; ?>
    <?php if ($error === 'time'):      ?><div class="alert alert--error">End time must be after start time.</div><?php endif; ?>
    <?php if ($error === 'overlap'):   ?><div class="alert alert--error">This session overlaps with another in the same room (requires 15 min gap).</div><?php endif; ?>

    <?php if (empty($schedules)): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">No sessions found</h3>
            <p class="page-card__text">There are no upcoming sessions for this selection.</p>
        </div>
    <?php else: ?>
        <section class="film-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));">
            <?php foreach ($schedules as $s): ?>
            <article class="schedule-card">
                <h3 class="schedule-card__film"><?php echo htmlspecialchars((string)$s->getFilmTitle(), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="schedule-card__meta">🎬 <?php echo htmlspecialchars((string)$s->getRoomName(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="schedule-card__meta">🕐 <?php echo date('M j, Y – H:i', strtotime((string)$s->getStartTime())); ?></p>
                <p class="schedule-card__price">$<?php echo number_format((float)$s->getTicketPrice(), 2); ?></p>

                <?php if ($isAdmin): ?>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <a href="/Cinema/schedules/edit?id=<?php echo $s->getId(); ?>" class="btn btn--secondary" style="flex:1;text-align:center;padding:7px 10px;font-size:.8rem;">Edit</a>
                        <form action="/Cinema/schedules/delete" method="POST" style="flex:1;margin:0;display:flex;" onsubmit="return confirm('Delete this session?');">
                            <input type="hidden" name="id" value="<?php echo $s->getId(); ?>">
                            <button type="submit" class="btn btn--primary" style="flex:1;padding:7px 10px;font-size:.8rem;background:#dc2626;">Delete</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="/Cinema/seats?schedule_id=<?php echo $s->getId(); ?>" class="btn btn--success">Select Seats</a>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

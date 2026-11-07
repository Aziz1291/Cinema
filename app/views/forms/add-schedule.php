<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
require_once __DIR__ . '/../../models/FilmModel.php';
require_once __DIR__ . '/../../models/RoomModel.php';

$selectedFilmId = (int)($_GET['film_id'] ?? 0);
$films = (new FilmModel())->getAllFilms();
$rooms = (new RoomModel())->getAllRooms();
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Session – Cinema Administration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
    <link rel="icon" href="/Cinema/public/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/Cinema/public/favicon.svg">
    <link rel="icon" type="image/png" sizes="48x48" href="/Cinema/public/favicon-48.png">
    <link rel="apple-touch-icon" href="/Cinema/public/apple-touch-icon.png">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>

<main class="page" id="main-content" tabindex="-1">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Screening Administration</p>
            <h2 class="page__title">Create Screening Session</h2>
            <div style="margin-top:6px;">
                <a href="/Cinema/schedules" class="back-link">&larr; Back to Sessions</a>
            </div>
        </div>
    </header>

    <?php if ($error === 'time'): ?>
        <div class="alert alert--error">⚠ Invalid timing: Start time must be valid and end time must follow.</div>
    <?php elseif ($error === 'overlap'): ?>
        <div class="alert alert--warning">
            <strong>Room Overlap Detected:</strong> Another screening already occupies this auditorium during that window. A <strong>15-minute gap</strong> is required between sessions.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form action="/Cinema/schedules/create" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label" for="film_id">Select Film</label>
                <select name="film_id" id="film_id" class="form-select" required autofocus>
                    <?php foreach ($films as $f): ?>
                        <option value="<?php echo $f->getId(); ?>" data-duration="<?php echo (int)$f->getDuration(); ?>" <?php echo $selectedFilmId === (int)$f->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string)$f->getTitle(), ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)$f->getDuration(); ?> min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="room_id">Select Auditorium Room</label>
                <select name="room_id" id="room_id" class="form-select" required>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?php echo $r->getId(); ?>">
                            <?php echo htmlspecialchars((string)$r->getName(), ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)$r->getSeatsNumber(); ?> seats)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="start_time">Screening Start Time</label>
                <input type="datetime-local" id="start_time" name="start_time" class="form-input" required min="<?= date('Y-m-d\TH:i') ?>">
                <div id="calculatedEndNote" class="form-hint" style="margin-top:6px; color:var(--accent);">
                    End time will be calculated automatically based on film duration (+15 min cleaning buffer).
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="ticket_price">Ticket Admission Price ($)</label>
                <input type="number" id="ticket_price" name="ticket_price" class="form-input" min="0" max="1000" step="0.5" value="12.00" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Program Session</button>
                <a href="/Cinema/schedules" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
(function() {
    const filmSelect = document.getElementById('film_id');
    const startInput = document.getElementById('start_time');
    const note = document.getElementById('calculatedEndNote');

    function updateEndTime() {
        if (!startInput.value) return;
        const selectedOption = filmSelect.options[filmSelect.selectedIndex];
        const duration = parseInt(selectedOption.getAttribute('data-duration'), 10) || 120;
        const startDate = new Date(startInput.value);
        if (isNaN(startDate.getTime())) return;

        const endDate = new Date(startDate.getTime() + duration * 60000);
        const freeDate = new Date(endDate.getTime() + 15 * 60000);

        const endStr = endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        const freeStr = freeDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        note.innerHTML = `Estimated finish: <strong>${endStr}</strong> &middot; Auditorium free after <strong>${freeStr}</strong> (15m buffer).`;
    }

    filmSelect.addEventListener('change', updateEndTime);
    startInput.addEventListener('change', updateEndTime);
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

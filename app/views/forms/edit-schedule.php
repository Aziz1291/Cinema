<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
require_once __DIR__ . '/../../models/ScheduleModel.php';
require_once __DIR__ . '/../../models/FilmModel.php';
require_once __DIR__ . '/../../models/RoomModel.php';
$schedule = (new ScheduleModel())->getScheduleById((int)($_GET['id'] ?? 0));
if (!$schedule) { header('Location: /Cinema/schedules'); exit; }
$films = (new FilmModel())->getAllFilms();
$rooms = (new RoomModel())->getAllRooms();
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Session – Admin</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>
<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Admin / Sessions</p>
        <h2 class="page__title">Edit Session</h2>
        <a href="/Cinema/schedules" class="back-link" style="margin-top:6px;">← Back to Sessions</a>
    </header>
    <section class="page-card" style="max-width:560px;">
        <form action="/Cinema/schedules/update" method="POST" style="display:grid;gap:14px;">
            <input type="hidden" name="id" value="<?php echo $schedule->getId(); ?>">
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Film</label>
                <select name="film_id" required style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:#1a2030;color:#f1f5f9;outline:none;font-size:.9rem;">
                    <?php foreach ($films as $f): ?>
                        <option value="<?php echo $f->getId(); ?>" <?php echo $schedule->getFilmId() == $f->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string)$f->getTitle(), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Room</label>
                <select name="room_id" required style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:#1a2030;color:#f1f5f9;outline:none;font-size:.9rem;">
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?php echo $r->getId(); ?>" <?php echo $schedule->getRoomId() == $r->getId() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string)$r->getName(), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Start Time <small>(End time will be calculated automatically)</small></label>
                <input type="datetime-local" name="start_time" required
                       value="<?php echo date('Y-m-d\TH:i', strtotime((string)$schedule->getStartTime())); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:grid;gap:6px;">
                <label style="font-size:.85rem;color:#94a3b8;">Ticket Price ($)</label>
                <input type="number" name="ticket_price" min="0" step="1" required
                       value="<?php echo (int)$schedule->getTicketPrice(); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;">
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <a href="/Cinema/schedules" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

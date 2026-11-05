<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['id'])) { header('Location: /Cinema/login'); exit; }
require_once __DIR__ . '/../../models/UserModel.php';
if (!(new UserModel())->isAdmin($_SESSION['id'])) { header('Location: /Cinema/dashboard'); exit; }
$currentUser = (new UserModel())->getUserById($_SESSION['id']);
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room – Cinema Administration</title>
    <link rel="icon" type="image/png" href="/Cinema/public/favicon.png">
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/../partials/topbar.php'; ?>

<main class="page">
    <header class="page__header">
        <div>
            <p class="kicker page__eyebrow">Venue Administration</p>
            <h2 class="page__title">Add Auditorium Room</h2>
            <div style="margin-top:6px;">
                <a href="/Cinema/rooms" class="back-link">&larr; Back to Rooms</a>
            </div>
        </div>
    </header>

    <div class="form-card">
        <form action="/Cinema/rooms/create" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label" for="room_name">Auditorium Name <span class="muted">(max 50 characters)</span></label>
                <input type="text" id="room_name" name="name" class="form-input" maxlength="50" placeholder="e.g. Hall 1 - Grand IMAX" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="room_rows">Number of Rows</label>
                <input type="number" id="room_rows" name="rowsNumber" class="form-input" min="1" max="26" placeholder="8" required>
                <span class="form-hint">Rows will be assigned sequential letters (A through Z).</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="room_seats">Total Seat Capacity</label>
                <input type="number" id="room_seats" name="seatsNumber" class="form-input" min="1" max="1000" placeholder="120" required>
                <span class="form-hint">Individual seats will be generated automatically in the database.</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Create Auditorium</button>
                <a href="/Cinema/rooms" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>

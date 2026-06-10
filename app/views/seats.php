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
require_once __DIR__ . '/../models/SeatModel.php';
require_once __DIR__ . '/../models/ScheduleModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';

$room_id     = isset($_GET['roomid'])      ? (int)$_GET['roomid']      : 0;
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

$schedule      = null;
$reserved_seats = [];

if ($schedule_id > 0) {
    $schedule = (new ScheduleModel())->getScheduleById($schedule_id);
    if ($schedule) {
        $room_id        = $schedule->getRoomId();
        $reserved_seats = (new ReservationModel())->getReservedSeatIdsByScheduleId($schedule_id);
    }
}

$room  = (new RoomModel())->getRoomById($room_id);
$seats = (new SeatModel())->getSeatsByRoomId($room_id);

$seatsByRow = [];
foreach ($seats as $s) {
    $seatsByRow[$s->getRowLetter()][] = $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seats – Cinema</title>
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header">
        <p class="page__eyebrow">Seating</p>
        <h2 class="page__title">
            <?php echo $room ? htmlspecialchars((string)$room->getName(), ENT_QUOTES, 'UTF-8') : 'Unknown Room'; ?>
        </h2>
        <p class="page__subtitle">
            <?php if ($schedule): ?>
                Booking for <strong style="color:#f1f5f9;"><?php echo htmlspecialchars((string)$schedule->getFilmTitle(), ENT_QUOTES, 'UTF-8'); ?></strong>
                &nbsp;·&nbsp; <?php echo date('M j, Y – H:i', strtotime((string)$schedule->getStartTime())); ?>
                &nbsp;·&nbsp; <span style="color:#e50914; font-weight:700;">$<?php echo number_format((float)$schedule->getTicketPrice(), 2); ?></span>
            <?php else: ?>
                <?php echo $isAdmin ? 'Admin view.' : 'Room seat map.'; ?>
            <?php endif; ?>
        </p>
        <?php if ($schedule): ?>
            <a href="/Cinema/schedules?film_id=<?php echo $schedule->getFilmId(); ?>" class="back-link" style="margin-top:8px;">← Back to Schedules</a>
        <?php else: ?>
            <a href="/Cinema/rooms" class="back-link" style="margin-top:8px;">← Back to Rooms</a>
        <?php endif; ?>
    </header>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'taken'): ?>
        <div class="alert alert--error">That seat was just taken — please choose another one.</div>
    <?php endif; ?>

    <?php if (!$room): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">Room not found</h3>
            <p class="page-card__text">The specified room does not exist.</p>
        </div>
    <?php elseif (empty($seats)): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">No seats configured</h3>
            <p class="page-card__text">No seats have been added to this room yet.</p>
        </div>
    <?php else: ?>
        <div class="cinema-wrap">
            <div class="cinema-screen">Screen</div>

            <?php if ($schedule && !$isAdmin): ?>
            <form action="/Cinema/reserve" method="POST" id="bookingForm">
                <input type="hidden" name="schedule_id" value="<?php echo $schedule->getId(); ?>">
            <?php endif; ?>

            <div class="cinema-rows">
                <?php foreach ($seatsByRow as $rowLetter => $rowSeats): ?>
                <div class="cinema-row">
                    <span class="cinema-row__label"><?php echo htmlspecialchars((string)$rowLetter, ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="cinema-row__seats">
                        <?php foreach ($rowSeats as $seat):
                            $isOccupied = in_array($seat->getId(), $reserved_seats);
                            $cls        = $isOccupied ? 'cinema-seat--occupied' : 'cinema-seat--standard';
                        ?>
                            <?php if ($schedule && !$isOccupied && !$isAdmin): ?>
                                <label style="margin:0; cursor:pointer;" title="Book seat">
                                    <input type="checkbox" name="seat_ids[]" value="<?php echo $seat->getId(); ?>" style="display:none;" onchange="this.nextElementSibling.classList.toggle('cinema-seat--standard', !this.checked); this.nextElementSibling.classList.toggle('cinema-seat--selected', this.checked);">
                                    <div class="cinema-seat cinema-seat--standard">
                                        <?php echo $seat->getSeatNumber(); ?>
                                    </div>
                                </label>
                            <?php else: ?>
                                <div class="cinema-seat <?php echo $cls; ?>"
                                     title="<?php echo $isOccupied ? 'Occupied' : 'Available'; ?>">
                                    <?php echo $seat->getSeatNumber(); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <span class="cinema-row__label"><?php echo htmlspecialchars((string)$rowLetter, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($schedule && !$isAdmin): ?>
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn--primary" style="font-size: 1.1rem; padding: 12px 30px;" onclick="return document.querySelectorAll('input[name=\'seat_ids[]\']:checked').length > 0 || (alert('Please select at least one seat.') && false);">Book Selected Seats</button>
                </div>
            </form>
            <?php endif; ?>

            <div class="cinema-legend">
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch cinema-seat--standard"></div> Available
                </div>
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch cinema-seat--selected"></div> Selected
                </div>
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch cinema-seat--occupied"></div> Occupied
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

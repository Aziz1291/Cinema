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

$room_id     = isset($_GET['roomid']) ? (int)$_GET['roomid'] : (isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0);
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

$schedule       = null;
$reserved_seats = [];

if ($schedule_id > 0) {
    $schedule = (new ScheduleModel())->getScheduleById($schedule_id);
    if ($schedule) {
        $room_id        = $schedule->getRoomId();
        $reserved_seats = (new ReservationModel())->getReservedSeatIdsByScheduleId($schedule_id);
    }
}

$room  = (new RoomModel())->getRoomById($room_id);
// A missing or removed screening or room gets the 404 page, not an empty seat plan.
if (!$room || ($schedule_id > 0 && !$schedule)) {
    not_found();
}
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
    <title>Seating Plan – Cinema</title>
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
            <p class="kicker page__eyebrow">Auditorium Seating</p>
            <h2 class="page__title">
                <?php echo $room ? htmlspecialchars((string)$room->getName(), ENT_QUOTES, 'UTF-8') : 'Unknown Auditorium'; ?>
            </h2>
            <p class="page__subtitle">
                <?php if ($schedule): ?>
                    Screening: <strong style="color:var(--ink);"><?php echo htmlspecialchars((string)$schedule->getFilmTitle(), ENT_QUOTES, 'UTF-8'); ?></strong>
                    &middot; <?php $st = strtotime((string)$schedule->getStartTime()); echo date('D, M j', $st) . ' · ' . date('H:i', $st); ?>
                    &middot; <span style="color:var(--accent); font-weight:700;">$<?php echo number_format((float)$schedule->getTicketPrice(), 2); ?> per seat</span>
                <?php else: ?>
                    <?php echo $isAdmin ? 'Admin Console &middot; Room capacity and physical seating matrix inspection.' : 'Seating layout overview.'; ?>
                <?php endif; ?>
            </p>
            <div style="margin-top:8px;">
                <?php if ($schedule): ?>
                    <a href="/Cinema/schedules?film_id=<?php echo $schedule->getFilmId(); ?>" class="back-link">&larr; Back to Sessions</a>
                <?php else: ?>
                    <a href="/Cinema/rooms" class="back-link">&larr; Back to Rooms</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'taken'): ?>
        <div class="alert alert--error">
            <strong>Booking Collision:</strong> One or more of your chosen seats was just reserved by another guest. Please choose alternative seats.
        </div>
    <?php endif; ?>

    <?php if (!$room): ?>
        <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
            <h3>Auditorium Not Found</h3>
            <p class="muted">The requested cinema room could not be found in our database.</p>
        </div>
    <?php elseif (empty($seats)): ?>
        <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
            <h3>No Seats Configured</h3>
            <p class="muted">This auditorium does not currently have seats registered.</p>
        </div>
    <?php else: ?>

        <!-- The Atmospheric Cinema Auditorium Seating View -->
        <section class="cinema-hall" aria-label="Cinema Seating Hall">
            <div class="cinema-screen-wrap">
                <svg viewBox="0 0 500 40" class="cinema-screen-svg" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="screenArcGlow" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#1d4ed8" stop-opacity="0.1"/>
                            <stop offset="50%" stop-color="#ffffff" stop-opacity="0.95"/>
                            <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.1"/>
                        </linearGradient>
                    </defs>
                    <path d="M 20,36 Q 250,6 480,36" fill="none" stroke="url(#screenArcGlow)" stroke-width="4"/>
                </svg>
                <span class="cinema-screen-label">Screen &middot; Front of Auditorium</span>
            </div>

            <?php if ($schedule && !$isAdmin): ?>
            <form action="/Cinema/reserve" method="POST" id="seatBookingForm" style="width:100%; display:flex; flex-direction:column; align-items:center;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="schedule_id" value="<?php echo $schedule->getId(); ?>">
                <input type="hidden" id="ticketPrice" value="<?php echo (float)$schedule->getTicketPrice(); ?>">
            <?php endif; ?>

            <div class="cinema-rows" role="region" aria-label="Seating Rows">
                <?php foreach ($seatsByRow as $rowLetter => $rowSeats): ?>
                <div class="cinema-row">
                    <span class="cinema-row__label" aria-hidden="true"><?php echo htmlspecialchars((string)$rowLetter, ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="cinema-row__seats">
                        <?php foreach ($rowSeats as $seat):
                            $seatId = (int)$seat->getId();
                            $seatNum = (int)$seat->getSeatNumber();
                            $isOccupied = in_array($seatId, $reserved_seats, true);
                            $seatLabel = $rowLetter . $seatNum;
                        ?>
                            <?php if ($schedule && !$isOccupied && !$isAdmin): ?>
                                <label style="margin:0; cursor:pointer;" title="Seat <?php echo $seatLabel; ?> &middot; Available ($<?php echo number_format((float)$schedule->getTicketPrice(), 2); ?>)">
                                    <input type="checkbox"
                                           name="seat_ids[]"
                                           value="<?php echo $seatId; ?>"
                                           data-seat-label="<?php echo $seatLabel; ?>"
                                           style="position:absolute; opacity:0; pointer-events:none;"
                                           class="seat-checkbox">
                                    <div class="cinema-seat" aria-label="Seat <?php echo $seatLabel; ?>, Available">
                                        <?php echo $seatNum; ?>
                                    </div>
                                </label>
                            <?php else: ?>
                                <div class="cinema-seat <?php echo $isOccupied ? 'cinema-seat--occupied' : ''; ?>"
                                     title="Seat <?php echo $seatLabel; ?> &middot; <?php echo $isOccupied ? 'Occupied' : 'Standard'; ?>"
                                     aria-label="Seat <?php echo $seatLabel; ?>, <?php echo $isOccupied ? 'Occupied' : 'Available'; ?>">
                                    <?php echo $seatNum; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <span class="cinema-row__label" aria-hidden="true"><?php echo htmlspecialchars((string)$rowLetter, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cinema-legend">
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch" style="background:rgba(255,255,255,0.08); border-color:rgba(255,255,255,0.2);"></div>
                    <span>Available</span>
                </div>
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch" style="background:var(--accent); border-color:#93c5fd;"></div>
                    <span>Selected</span>
                </div>
                <div class="cinema-legend__item">
                    <div class="cinema-legend__swatch" style="background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.05);"></div>
                    <span>Occupied</span>
                </div>
            </div>

            <?php if ($schedule && !$isAdmin): ?>
            <!-- Floating Reservation Dock -->
            <div class="reservation-dock" id="reservationDock" style="margin-top:20px;">
                <div class="reservation-dock__info">
                    <span class="reservation-dock__label">Selected Seats</span>
                    <span class="reservation-dock__seats" id="selectedSeatsLabel">None selected</span>
                </div>

                <div class="reservation-dock__pricing">
                    <span class="muted" style="font-size:0.85rem;">Total:</span>
                    <span class="reservation-dock__price" id="totalPriceDisplay">$0.00</span>
                </div>

                <button type="submit" id="reserveButton" class="btn btn--primary btn--lg" disabled>
                    Book Selected Seats &rarr;
                </button>
            </div>
            </form>
            <?php endif; ?>

        </section>

    <?php endif; ?>
</main>

<?php if ($schedule && !$isAdmin): ?>
<script>
(function() {
    const checkboxes = document.querySelectorAll('.seat-checkbox');
    const seatsLabel = document.getElementById('selectedSeatsLabel');
    const priceDisplay = document.getElementById('totalPriceDisplay');
    const reserveBtn = document.getElementById('reserveButton');
    const unitPrice = parseFloat(document.getElementById('ticketPrice').value) || 0;

    function updateSelection() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const count = selected.length;

        // Toggle visual selected class
        checkboxes.forEach(cb => {
            const seatDiv = cb.nextElementSibling;
            if (seatDiv) {
                seatDiv.classList.toggle('cinema-seat--selected', cb.checked);
            }
        });

        if (count > 0) {
            const labels = selected.map(cb => cb.dataset.seatLabel).join(', ');
            seatsLabel.textContent = labels + ' (' + count + ' ticket' + (count > 1 ? 's' : '') + ')';
            priceDisplay.textContent = '$' + (count * unitPrice).toFixed(2);
            reserveBtn.disabled = false;
        } else {
            seatsLabel.textContent = 'None selected';
            priceDisplay.textContent = '$0.00';
            reserveBtn.disabled = true;
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });

    document.getElementById('seatBookingForm').addEventListener('submit', function(e) {
        const count = document.querySelectorAll('.seat-checkbox:checked').length;
        if (count === 0) {
            e.preventDefault();
            alert('Please click on the seating map to select at least one seat before booking.');
        }
    });
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

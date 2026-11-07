<?php
// Keep application errors in server logs instead of exposing paths and queries.
require_once __DIR__ . '/../app/error-page.php';

// Limits follow the database columns (films.title VARCHAR(100), films.description
// VARCHAR(255), rooms.name VARCHAR(50)); rows are lettered A to Z.
const FILM_RULES = [
    'title'       => ['Title', 'text', 1, 100],
    'duration'    => ['Duration (minutes)', 'int', 1, 480],
    'description' => ['Description', 'text', 1, 255],
];
const ROOM_RULES = [
    'name'        => ['Room name', 'text', 1, 50],
    'rowsNumber'  => ['Number of rows', 'int', 1, 26],
    'seatsNumber' => ['Number of seats', 'int', 1, 1000],
];
const SCHEDULE_RULES = [
    'film_id'      => ['Film', 'int', 1, PHP_INT_MAX],
    'room_id'      => ['Room', 'int', 1, PHP_INT_MAX],
    'ticket_price' => ['Ticket price', 'float', 0, 1000],
];

/** Validates a session form; returns the cleaned values and start time, or sends the admin back. */
function valid_schedule()
{
    $problem = validate_fields(SCHEDULE_RULES, $schedule);
    $start = strtotime((string) ($_POST['start_time'] ?? ''));
    if (!$problem && $start === false) $problem = 'Start time is not a valid date and time.';
    if (!$problem && $start < time()) $problem = 'A session cannot start in the past.';
    if ($problem) fail_to('/Cinema/schedules', $problem);
    $schedule['start_time'] = date('Y-m-d H:i:s', $start);
    return $schedule;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize path to always start with /Cinema so local routes work on Render
if (strpos($path, '/Cinema') !== 0) {
    if (strpos($path, '/public') === 0) {
        $path = '/Cinema' . substr($path, 7);
    } else {
        $path = '/Cinema' . ($path === '/' ? '/' : $path);
    }
}
if ($path === '/Cinema/') {
    $path = '/Cinema';
}

// Only the public demo entry is intended for indexing, not account or admin pages.
if ($path !== '/Cinema') {
    header('X-Robots-Tag: noindex, nofollow', true);
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $isHttps]);
session_start();
require_once __DIR__ . '/../app/security.php';

// The demo notice is available without an account and acknowledgement is
// remembered only for this PHP session. No optional tracking is enabled here.
if ($path === '/Cinema/privacy') {
    require __DIR__ . '/../app/views/privacy.php';
    exit;
}
if ($path === '/Cinema/acknowledge-privacy') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /Cinema/');
        exit;
    }
    // A refused acknowledgement shows the notice again with a reason instead of a blank page.
    if (!csrf_valid()) {
        $gateError = 'Your request expired. Please tick the box and try again.';
        $gateStatus = 403;
    } elseif (($_POST['privacy_acknowledgement'] ?? '') !== 'yes') {
        $gateError = 'Please read and tick the privacy notice to continue.';
        $gateStatus = 400;
    } else {
        $_SESSION['privacy_acknowledged'] = true;
        header('Location: /Cinema/');
        exit;
    }
    http_response_code($gateStatus);
    require __DIR__ . '/../app/views/privacy-gate.php';
    exit;
}
if (empty($_SESSION['privacy_acknowledged'])) {
    // Pages and actions stay closed until the notice is acknowledged.
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) http_response_code(403);
    require __DIR__ . '/../app/views/privacy-gate.php';
    exit;
}
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/entities/User.php';

$protectedRoutes = [
    '/Cinema/dashboard',
    '/Cinema/rooms',
    '/Cinema/seats', '/Cinema/seat',
    '/Cinema/films',
    '/Cinema/schedules',
    '/Cinema/reservations', '/Cinema/reservations/cancel', '/Cinema/reserve',
    '/Cinema/films/add', '/Cinema/films/edit',
    '/Cinema/rooms/add', '/Cinema/rooms/edit',
    '/Cinema/schedules/add', '/Cinema/schedules/edit',
];

if (in_array($path, $protectedRoutes, true) && empty($_SESSION['id'])) {
    header('Location: /Cinema/login');
    exit;
}

// State-changing routes: POST only, with a valid CSRF token
$postOnlyRoutes = [
    '/Cinema/loginUser', '/Cinema/registerUser', '/Cinema/reserve', '/Cinema/reservations/cancel',
    '/Cinema/films/create', '/Cinema/films/update', '/Cinema/films/delete',
    '/Cinema/rooms/create', '/Cinema/rooms/update', '/Cinema/rooms/delete',
    '/Cinema/schedules/create', '/Cinema/schedules/update', '/Cinema/schedules/delete',
];
if (in_array($path, $postOnlyRoutes, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid or missing CSRF token. Go back, reload the page and try again.');
    }
}

// Helper: signed-out visitors go to the login page; customers get a clear 403 page.
function requireAdmin() {
    if (empty($_SESSION['id'])) {
        header('Location: /Cinema/login'); exit;
    }
    if (!(new UserModel())->isAdmin($_SESSION['id'])) {
        http_response_code(403);
        require __DIR__ . '/../app/views/403.php';
        exit;
    }
}

switch ($path) {
    // ── Root ────────────────────────────────────────────────
    case '/Cinema':
    case '/Cinema/':
        header('Location: ' . (!empty($_SESSION['id']) ? '/Cinema/dashboard' : '/Cinema/login'));
        break;

    // ── Auth ────────────────────────────────────────────────
    case '/Cinema/login':
        require_once __DIR__ . '/../app/views/login.php';
        break;

    case '/Cinema/loginUser':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $authResult = $authController->verifyUser();
        if ($authResult === 'success') {
            $user = (new UserModel())->getUserByUsernameOrEmail($_POST['username']);
            session_regenerate_id(true);
            $_SESSION['id'] = $user->getId();
            header('Location: /Cinema/dashboard');
        } else {
            header('Location: /Cinema/login?error=' . urlencode($authResult));
        }
        break;

    case '/Cinema/register':
        require_once __DIR__ . '/../app/views/register.php';
        break;

    case '/Cinema/registerUser':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $authController = new AuthController();
        $userModel      = new UserModel();
        $registered = $authController->userRegister();
        if ($registered === true) {
            $user = $userModel->getUserByUsernameOrEmail(trim($_POST['username']));
            session_regenerate_id(true);
            $_SESSION['id'] = $user->getId();
            header('Location: /Cinema/dashboard');
        } else {
            $_SESSION['form_error'] = $registered;
            header('Location: /Cinema/register?error=validation');
        }
        break;

    case '/Cinema/logout':
        // Signing out needs a POST with the session token, so a link on another site cannot sign people out.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
            session_unset(); session_destroy();
            header('Location: /Cinema/login');
            break;
        }
        if (empty($_SESSION['id'])) {
            header('Location: /Cinema/login');
            break;
        }
        require_once __DIR__ . '/../app/views/sign-out.php';
        break;

    case '/Cinema/dashboard':
        require_once __DIR__ . '/../app/views/dashboard.php';
        break;

    case '/Cinema/films':
        require_once __DIR__ . '/../app/views/films.php';
        break;

    case '/Cinema/rooms':
        require_once __DIR__ . '/../app/views/rooms.php';
        break;

    case '/Cinema/seats':
    case '/Cinema/seat':
        require_once __DIR__ . '/../app/views/seats.php';
        break;

    case '/Cinema/schedules':
        require_once __DIR__ . '/../app/views/schedules.php';
        break;

    case '/Cinema/reservations':
        require_once __DIR__ . '/../app/views/users/reservations.php';
        break;

    case '/Cinema/reserve':
        require_once __DIR__ . '/../app/models/ReservationModel.php';
        $isAdminReserve = isset($_SESSION['id']) && (new UserModel())->isAdmin($_SESSION['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id']) && !$isAdminReserve) {
            $scheduleId = (int)($_POST['schedule_id'] ?? 0);
            $seatIds = $_POST['seat_ids'] ?? [];
            if (!is_array($seatIds)) $seatIds = [$seatIds];
            $seatIds = array_values(array_unique(array_map('intval', $seatIds)));

            $ok = false;
            if ($scheduleId > 0 && $seatIds) {
                require_once __DIR__ . '/../app/models/ScheduleModel.php';
                require_once __DIR__ . '/../app/models/SeatModel.php';
                $schedule  = (new ScheduleModel())->getScheduleById($scheduleId);
                $seatModel = new SeatModel();
                $valid = $schedule !== null;
                // every seat must exist and belong to the room of the chosen session
                foreach ($seatIds as $seatId) {
                    $seat = $seatModel->getSeatById($seatId);
                    if (!$seat || !$schedule || (int)$seat->getRoomId() !== (int)$schedule->getRoomId()) {
                        $valid = false;
                        break;
                    }
                }
                if ($valid) {
                    $ok = (new ReservationModel())->createReservations($_SESSION['id'], $seatIds, $scheduleId);
                }
            }

            if ($ok) {
                go_flash('/Cinema/reservations', 'success', 'booked');
            }
            go_flash('/Cinema/seats?schedule_id=' . $scheduleId, 'error', 'taken');
        } else {
            header('Location: /Cinema/dashboard');
        }
        break;

    case '/Cinema/reservations/cancel':
        // Customers cancel their own upcoming bookings; admins can cancel any booking.
        require_once __DIR__ . '/../app/models/ReservationModel.php';
        $reservationModel = new ReservationModel();
        $booking = $reservationModel->getReservationById((int)($_POST['id'] ?? 0));
        $adminCancel = (new UserModel())->isAdmin($_SESSION['id']);
        $allowed = $booking && ($adminCancel
            || ((int)$booking['user_id'] === (int)$_SESSION['id'] && strtotime($booking['start_time']) > time()));
        if (!$allowed) {
            go_flash('/Cinema/reservations', 'error', 'cancel');
        }
        $reservationModel->cancelReservation((int)$booking['id']);
        go_flash('/Cinema/reservations', 'success', 'cancelled');
        break;

    case '/Cinema/films/add':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/add-film.php';
        break;

    case '/Cinema/films/edit':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/edit-film.php';
        break;

    case '/Cinema/rooms/add':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/add-room.php';
        break;

    case '/Cinema/rooms/edit':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/edit-room.php';
        break;

    case '/Cinema/schedules/add':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/add-schedule.php';
        break;

    case '/Cinema/schedules/edit':
        requireAdmin();
        require_once __DIR__ . '/../app/views/forms/edit-schedule.php';
        break;

    case '/Cinema/films/create':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $problem = validate_fields(FILM_RULES, $film);
        if ($problem) fail_to('/Cinema/films', $problem);
        // ── Handle poster upload ───────────────────────────
        $posterFilename = save_poster($_FILES['poster_image'] ?? [], $posterError) ?? '';
        if ($posterError) fail_to('/Cinema/films', $posterError);
        $ok = (new FilmModel())->createFilm($film['title'], $film['duration'], $film['description'], $posterFilename);
        go_flash('/Cinema/films', $ok ? 'success' : 'error', $ok ? 'created' : 'db');
        break;

    case '/Cinema/films/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $problem = validate_fields(FILM_RULES, $film);
        if ($problem) fail_to('/Cinema/films', $problem);
        // ── Handle poster upload ───────────────────────────
        $posterFilename = save_poster($_FILES['poster_image'] ?? [], $posterError) ?? basename((string)($_POST['old_poster'] ?? ''));
        if ($posterError) fail_to('/Cinema/films', $posterError);
        $ok = (new FilmModel())->updateFilm($_POST['id'], $film['title'], $film['duration'], $film['description'], $posterFilename);
        go_flash('/Cinema/films', $ok ? 'success' : 'error', $ok ? 'updated' : 'db');
        break;

    case '/Cinema/films/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $ok = (new FilmModel())->deleteFilm($_POST['id']);
        go_flash('/Cinema/films', $ok ? 'success' : 'error', $ok ? 'deleted' : 'db');
        break;

    // Rooms
    case '/Cinema/rooms/create':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $problem = validate_fields(ROOM_RULES, $room);
        if (!$problem && $room['seatsNumber'] < $room['rowsNumber']) $problem = 'A room needs at least one seat per row.';
        if ($problem) fail_to('/Cinema/rooms', $problem);
        $ok = (new RoomModel())->createRoom($room['name'], $room['rowsNumber'], $room['seatsNumber']);
        go_flash('/Cinema/rooms', $ok ? 'success' : 'error', $ok ? 'created' : 'db');
        break;

    case '/Cinema/rooms/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $problem = validate_fields(ROOM_RULES, $room);
        if (!$problem && $room['seatsNumber'] < $room['rowsNumber']) $problem = 'A room needs at least one seat per row.';
        if ($problem) fail_to('/Cinema/rooms', $problem);
        $ok = (new RoomModel())->updateRoom($_POST['id'], $room['name'], $room['rowsNumber'], $room['seatsNumber']);
        go_flash('/Cinema/rooms', $ok ? 'success' : 'error', $ok ? 'updated' : 'db');
        break;

    case '/Cinema/rooms/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $ok = (new RoomModel())->deleteRoom($_POST['id']);
        go_flash('/Cinema/rooms', $ok ? 'success' : 'error', $ok ? 'deleted' : 'db');
        break;

    // Schedules
    case '/Cinema/schedules/create':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $s = valid_schedule();
        $film = (new FilmModel())->getFilmById($s['film_id']);
        if (!$film) fail_to('/Cinema/schedules', 'Choose an existing film.');
        $endTime = date('Y-m-d H:i:s', strtotime($s['start_time'] . " + " . (int)$film->getDuration() . " minutes"));

        $scheduleModel = new ScheduleModel();
        if ($scheduleModel->hasOverlap($s['room_id'], $s['start_time'], $endTime)) {
            go_flash('/Cinema/schedules', 'error', 'overlap');
            break;
        }

        $ok = $scheduleModel->createSchedule($s['film_id'], $s['room_id'], $s['start_time'], $endTime, $s['ticket_price']);
        go_flash('/Cinema/schedules', $ok ? 'success' : 'error', $ok ? 'created' : 'db');
        break;

    case '/Cinema/schedules/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $s = valid_schedule();
        $film = (new FilmModel())->getFilmById($s['film_id']);
        if (!$film) fail_to('/Cinema/schedules', 'Choose an existing film.');
        $endTime = date('Y-m-d H:i:s', strtotime($s['start_time'] . " + " . (int)$film->getDuration() . " minutes"));

        $scheduleModel = new ScheduleModel();
        if ($scheduleModel->hasOverlap($s['room_id'], $s['start_time'], $endTime, $_POST['id'])) {
            go_flash('/Cinema/schedules', 'error', 'overlap');
            break;
        }

        $ok = $scheduleModel->updateSchedule($_POST['id'], $s['film_id'], $s['room_id'], $s['start_time'], $endTime, $s['ticket_price']);
        go_flash('/Cinema/schedules', $ok ? 'success' : 'error', $ok ? 'updated' : 'db');
        break;

    case '/Cinema/schedules/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        $ok = (new ScheduleModel())->deleteSchedule($_POST['id']);
        go_flash('/Cinema/schedules', $ok ? 'success' : 'error', $ok ? 'deleted' : 'db');
        break;

    default:
        not_found();
}
?>

<?php
// Keep application errors in server logs instead of exposing paths and queries.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
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
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid or expired request. Please reload the notice and try again.');
    }
    if (($_POST['privacy_acknowledgement'] ?? '') !== 'yes') {
        http_response_code(400);
        exit('Please read and acknowledge the privacy notice.');
    }
    $_SESSION['privacy_acknowledged'] = true;
    header('Location: /Cinema/');
    exit;
}
if (empty($_SESSION['privacy_acknowledged'])) {
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
    '/Cinema/reservations',
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
    '/Cinema/loginUser', '/Cinema/registerUser', '/Cinema/reserve',
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

// Helper: check admin, redirect non-admins
function requireAdmin() {
    if (empty($_SESSION['id']) || !(new UserModel())->isAdmin($_SESSION['id'])) {
        header('Location: /Cinema/dashboard'); exit;
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
        if ($authController->userRegister()) {
            $user = $userModel->getUserByUsernameOrEmail($_POST['username']);
            session_regenerate_id(true);
            $_SESSION['id'] = $user->getId();
            header('Location: /Cinema/dashboard');
        } else {
            header('Location: /Cinema/register?error=validation');
        }
        break;

    case '/Cinema/logout':
        session_unset(); session_destroy();
        header('Location: /Cinema/login');
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

            header('Location: ' . ($ok
                ? '/Cinema/reservations?success=1'
                : '/Cinema/seats?schedule_id=' . $scheduleId . '&error=taken'));
        } else {
            header('Location: /Cinema/dashboard');
        }
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
        // ── Handle poster upload ───────────────────────────
        $posterFilename = save_poster($_FILES['poster_image'] ?? []) ?? '';
        $ok = (new FilmModel())->createFilm($_POST['title'], $_POST['duration'], $_POST['description'], $posterFilename);
        header('Location: /Cinema/films?' . ($ok ? 'success=created' : 'error=db'));
        break;

    case '/Cinema/films/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        // ── Handle poster upload ───────────────────────────
        $posterFilename = save_poster($_FILES['poster_image'] ?? []) ?? basename((string)($_POST['old_poster'] ?? ''));
        $ok = (new FilmModel())->updateFilm($_POST['id'], $_POST['title'], $_POST['duration'], $_POST['description'], $posterFilename);
        header('Location: /Cinema/films?' . ($ok ? 'success=updated' : 'error=db'));
        break;

    case '/Cinema/films/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $ok = (new FilmModel())->deleteFilm($_POST['id']);
        header('Location: /Cinema/films?' . ($ok ? 'success=deleted' : 'error=db'));
        break;

    // Rooms
    case '/Cinema/rooms/create':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $ok = (new RoomModel())->createRoom($_POST['name'], $_POST['rowsNumber'], $_POST['seatsNumber']);
        header('Location: /Cinema/rooms?' . ($ok ? 'success=created' : 'error=db'));
        break;

    case '/Cinema/rooms/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $ok = (new RoomModel())->updateRoom($_POST['id'], $_POST['name'], $_POST['rowsNumber'], $_POST['seatsNumber']);
        header('Location: /Cinema/rooms?' . ($ok ? 'success=updated' : 'error=db'));
        break;

    case '/Cinema/rooms/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/RoomModel.php';
        $ok = (new RoomModel())->deleteRoom($_POST['id']);
        header('Location: /Cinema/rooms?' . ($ok ? 'success=deleted' : 'error=db'));
        break;

    // Schedules
    case '/Cinema/schedules/create':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $film = (new FilmModel())->getFilmById($_POST['film_id']);
        $duration = $film ? (int)$film->getDuration() : 0;
        $endTime = date('Y-m-d H:i:s', strtotime($_POST['start_time'] . " + " . $duration . " minutes"));
        
        $scheduleModel = new ScheduleModel();
        if ($scheduleModel->hasOverlap($_POST['room_id'], $_POST['start_time'], $endTime)) {
            header('Location: /Cinema/schedules?error=overlap');
            break;
        }

        $ok = $scheduleModel->createSchedule($_POST['film_id'], $_POST['room_id'], $_POST['start_time'], $endTime, $_POST['ticket_price']);
        header('Location: /Cinema/schedules?' . ($ok ? 'success=created' : 'error=db'));
        break;

    case '/Cinema/schedules/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        require_once __DIR__ . '/../app/models/FilmModel.php';
        $film = (new FilmModel())->getFilmById($_POST['film_id']);
        $duration = $film ? (int)$film->getDuration() : 0;
        $endTime = date('Y-m-d H:i:s', strtotime($_POST['start_time'] . " + " . $duration . " minutes"));

        $scheduleModel = new ScheduleModel();
        if ($scheduleModel->hasOverlap($_POST['room_id'], $_POST['start_time'], $endTime, $_POST['id'])) {
            header('Location: /Cinema/schedules?error=overlap');
            break;
        }

        $ok = $scheduleModel->updateSchedule($_POST['id'], $_POST['film_id'], $_POST['room_id'], $_POST['start_time'], $endTime, $_POST['ticket_price']);
        header('Location: /Cinema/schedules?' . ($ok ? 'success=updated' : 'error=db'));
        break;

    case '/Cinema/schedules/delete':
        requireAdmin();
        require_once __DIR__ . '/../app/models/ScheduleModel.php';
        $ok = (new ScheduleModel())->deleteSchedule($_POST['id']);
        header('Location: /Cinema/schedules?' . ($ok ? 'success=deleted' : 'error=db'));
        break;
}
?>

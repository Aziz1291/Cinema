<?php
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

session_start();
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
            $reservationModel = new ReservationModel();
            $seatIds = $_POST['seat_ids'] ?? [];
            if (!is_array($seatIds)) $seatIds = [$seatIds];
            
            $successCount = 0;
            foreach ($seatIds as $seat_id) {
                if ($reservationModel->createReservation($_SESSION['id'], $seat_id, $_POST['schedule_id'])) {
                    $successCount++;
                }
            }
            
            header('Location: ' . ($successCount > 0 
                ? '/Cinema/reservations?success=1' 
                : '/Cinema/seats?schedule_id=' . $_POST['schedule_id'] . '&error=taken'));
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
        $posterFilename = '';
        if (!empty($_FILES['poster_image']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['poster_image']['tmp_name']);
            if (in_array($ftype, $allowed) && $_FILES['poster_image']['size'] <= 5 * 1024 * 1024) {
                $ext  = pathinfo($_FILES['poster_image']['name'], PATHINFO_EXTENSION);
                $dest = __DIR__ . '/../public/assets/' . basename($_FILES['poster_image']['name']);
                if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $dest)) {
                    $posterFilename = basename($_FILES['poster_image']['name']);
                }
            }
        }
        $ok = (new FilmModel())->createFilm($_POST['title'], $_POST['duration'], $_POST['description'], $posterFilename);
        header('Location: /Cinema/films?' . ($ok ? 'success=created' : 'error=db'));
        break;

    case '/Cinema/films/update':
        requireAdmin();
        require_once __DIR__ . '/../app/models/FilmModel.php';
        // ── Handle poster upload ───────────────────────────
        $posterFilename = $_POST['old_poster'] ?? ''; // keep existing by default
        if (!empty($_FILES['poster_image']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['poster_image']['tmp_name']);
            if (in_array($ftype, $allowed) && $_FILES['poster_image']['size'] <= 5 * 1024 * 1024) {
                $dest = __DIR__ . '/../public/assets/' . basename($_FILES['poster_image']['name']);
                if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $dest)) {
                    $posterFilename = basename($_FILES['poster_image']['name']);
                }
            }
        }
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
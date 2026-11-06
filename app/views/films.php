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
require_once __DIR__ . '/../models/FilmModel.php';
$search  = $_GET['search']  ?? '';
$films   = (new FilmModel())->getAllFilms($search);
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Now Playing – Cinema</title>
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
            <p class="kicker page__eyebrow">Theatrical Programme</p>
            <h2 class="page__title">Now Playing</h2>
            <p class="page__subtitle"><?php echo $isAdmin ? 'Admin Console &middot; Curate films, update synopses, and schedule screenings.' : 'Explore current releases and reserve your seats across all auditoriums.'; ?></p>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/films/add" class="btn btn--primary">+ Add Film</a>
        <?php endif; ?>
    </header>

    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
        <form method="GET" action="/Cinema/films" style="display:flex; gap:10px; width:min(100%, 460px);">
            <input type="text" name="search" class="form-input" placeholder="Search films by title or keyword..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn--secondary">Search</button>
            <?php if (!empty($search)): ?>
                <a href="/Cinema/films" class="btn btn--outline" title="Clear search">Clear</a>
            <?php endif; ?>
        </form>
        <?php if (!empty($search)): ?>
            <p class="muted" style="font-size:0.9rem;">Filtering by: <strong>&ldquo;<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>&rdquo;</strong> (<?php echo count($films); ?> found)</p>
        <?php endif; ?>
    </div>

    <?php if ($success === 'created'): ?><div class="alert alert--success">✓ Film added successfully to the catalog.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">✓ Film updated successfully.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">✓ Film removed from programme.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">⚠ Database transaction error. Please try again.</div><?php endif; ?>

    <?php if (empty($films)): ?>
        <div class="stat-card" style="text-align:center; padding:56px 24px; max-width:600px; margin:20px auto;">
            
            <h3 style="margin-bottom:8px;">No films found</h3>
            <p class="muted" style="margin-bottom:20px;">
                <?php echo !empty($search) ? 'No films match your search query. Try searching for something else.' : 'There are currently no films in the catalogue.'; ?>
            </p>
            <?php if ($isAdmin): ?>
                <a href="/Cinema/films/add" class="btn btn--primary" style="margin:0 auto;">+ Add First Film</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <section class="film-grid" aria-label="Films List">
            <?php foreach ($films as $film):
                $title   = (string) $film->getTitle();
                $initial = $title !== '' ? strtoupper($title[0]) : 'F';
                $poster  = $film->getPosterImage();
                $activeSessions = (int)$film->getActiveSessionsCount();
                $duration = (int)$film->getDuration();
            ?>
            <article class="film-card">
                <div class="film-card__poster">
                    <?php if ($poster): ?>
                        <img src="/Cinema/public/assets/<?php echo htmlspecialchars((string)$poster, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> official poster"
                             loading="lazy">
                    <?php else: ?>
                        <div style="display:grid; place-items:center; width:100%; height:100%; background:var(--tint); color:var(--muted); font-size:3.5rem; font-weight:800;">
                            <?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <span class="film-card__badge-duration">⏱ <?php echo $duration; ?>m</span>
                </div>

                <div class="film-card__body">
                    <div class="film-card__meta-bar">
                        <?php if ($activeSessions > 0): ?>
                            <span class="film-card__tag tone-olive">
                                &#9679; <?php echo $activeSessions; ?> active session<?php echo $activeSessions !== 1 ? 's' : ''; ?>
                            </span>
                        <?php else: ?>
                            <span class="film-card__tag" style="background:var(--tint); color:var(--muted);">
                                No upcoming sessions
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="film-card__title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="film-card__description"><?php echo htmlspecialchars((string)$film->getDescription(), ENT_QUOTES, 'UTF-8'); ?></p>

                    <div class="film-card__actions">
                        <?php if ($isAdmin): ?>
                            <a href="/Cinema/films/edit?id=<?php echo $film->getId(); ?>" class="btn btn--secondary btn--sm">Edit</a>
                            <form action="/Cinema/films/delete" method="POST" style="margin:0; display:inline-block;" onsubmit="return confirm('Delete \'<?php echo htmlspecialchars(addslashes($title), ENT_QUOTES, 'UTF-8'); ?>\'? All associated sessions will be removed.');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $film->getId(); ?>">
                                <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                            </form>
                            <a href="/Cinema/schedules?film_id=<?php echo $film->getId(); ?>" class="btn btn--outline btn--sm" style="margin-left:auto;">Sessions</a>
                        <?php else: ?>
                            <?php if ($activeSessions > 0): ?>
                                <a href="/Cinema/schedules?film_id=<?php echo $film->getId(); ?>" class="btn btn--primary btn--sm" style="width:100%;">Book Tickets &rarr;</a>
                            <?php else: ?>
                                <span class="btn btn--outline btn--sm" style="width:100%; opacity:0.6; cursor:not-allowed;">Unavailable</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

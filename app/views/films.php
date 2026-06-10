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
    <link rel="stylesheet" href="/Cinema/public/css/layout.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php require_once __DIR__ . '/partials/topbar.php'; ?>

<main class="page">
    <header class="page__header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <p class="page__eyebrow">Catalogue</p>
            <h2 class="page__title">Now Playing</h2>
            <p class="page__subtitle"><?php echo $isAdmin ? 'Admin view – add, edit or delete films.' : 'Browse our latest movies.'; ?></p>
        </div>
        <?php if ($isAdmin): ?>
            <a href="/Cinema/films/add" class="btn btn--primary" style="align-self:center;">+ Add Film</a>
        <?php endif; ?>
        <div style="flex-basis:100%;margin-top:4px;">
            <form method="GET" action="/Cinema/films" style="display:flex;gap:10px;max-width:500px;">
                <input type="text" name="search" placeholder="Search films by name..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                       style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#f1f5f9;outline:none;font-size:.9rem;flex:1;">
                <button type="submit" class="btn btn--secondary">Search</button>
            </form>
        </div>
    </header>

    <?php if ($success === 'created'): ?><div class="alert alert--success">Film added successfully.</div><?php endif; ?>
    <?php if ($success === 'updated'): ?><div class="alert alert--success">Film updated successfully.</div><?php endif; ?>
    <?php if ($success === 'deleted'): ?><div class="alert alert--success">Film deleted.</div><?php endif; ?>
    <?php if ($error === 'db'):        ?><div class="alert alert--error">Something went wrong. Please try again.</div><?php endif; ?>

    <?php if (empty($films)): ?>
        <div class="page-card page-card--wide">
            <h3 class="page-card__title">No films yet</h3>
            <p class="page-card__text">Add films to the database to see them here.</p>
        </div>
    <?php else: ?>
        <section class="film-grid" style="grid-template-columns: 1fr;">
            <?php foreach ($films as $film):
                $title   = (string) $film->getTitle();
                $initial = $title !== '' ? strtoupper($title[0]) : 'F';
                $poster  = $film->getPosterImage();
                $activeSessions = (int)$film->getActiveSessionsCount();
            ?>
            <article class="film-card" style="flex-direction:row;align-items:flex-start;text-align:left;padding:16px;gap:20px;">
                <div class="film-card__poster" style="width:120px;height:180px;flex-shrink:0;margin:0;border-radius:8px;overflow:hidden;">
                    <?php if ($poster): ?>
                        <img src="/Cinema/public/assets/<?php echo htmlspecialchars((string)$poster, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> poster" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:#1a2030;font-size:3rem;color:#334155;"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="film-card__body" style="flex:1;display:flex;flex-direction:column;justify-content:center;">
                    <h3 class="film-card__title" style="font-size:1.4rem;margin-bottom:6px;"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div style="display:flex;gap:12px;margin-bottom:12px;">
                        <span class="film-card__meta" style="margin:0;">⏱ <?php echo (int)$film->getDuration(); ?> min</span>
                        <span class="film-card__meta" style="margin:0;color:<?php echo $activeSessions > 0 ? '#10b981' : '#64748b'; ?>;">
                            🎟 <?php echo $activeSessions; ?> active session<?php echo $activeSessions !== 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <p class="film-card__text" style="font-size:.95rem;margin-bottom:16px;line-height:1.5;"><?php echo htmlspecialchars((string)$film->getDescription(), ENT_QUOTES, 'UTF-8'); ?></p>

                    <?php if ($isAdmin): ?>
                        <div style="display:flex;gap:8px;">
                            <a href="/Cinema/films/edit?id=<?php echo $film->getId(); ?>" class="btn btn--secondary" style="padding:8px 16px;font-size:.85rem;">Edit Film</a>
                            <form action="/Cinema/films/delete" method="POST" style="margin:0;display:inline-block;" onsubmit="return confirm('Delete this film?');">
                                <input type="hidden" name="id" value="<?php echo $film->getId(); ?>">
                                <button type="submit" class="btn btn--primary" style="padding:8px 16px;font-size:.85rem;background:#dc2626;">Delete Film</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div>
                            <?php if ($activeSessions > 0): ?>
                                <a href="/Cinema/schedules?film_id=<?php echo $film->getId(); ?>" class="btn btn--primary">Book Tickets</a>
                            <?php else: ?>
                                <span style="display:inline-block;padding:8px 16px;background:rgba(255,255,255,0.05);color:#64748b;border-radius:8px;font-size:.85rem;font-weight:600;">No sessions available</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

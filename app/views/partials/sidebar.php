<?php
$displayName = 'Guest';
$isLoggedIn = false;
$isAdmin = false;

if (isset($currentUser) && $currentUser) {
	$firstName = $currentUser->getFirstName();
	$username = $currentUser->getUsername();
	$displayName = $firstName ? $firstName : $username;
	$isLoggedIn = true;
	$isAdmin = $currentUser->getRole() === 'admin';
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function getActive($path, $uri) {
    if ($path === '/Cinema/dashboard' && ($uri === '/Cinema/dashboard' || $uri === '/Cinema/')) return ' sidebar__link--active';
    if ($path !== '/Cinema/dashboard' && strpos((string)$uri, $path) === 0) return ' sidebar__link--active';
    return '';
}
?>

<aside class="sidebar">
	<div class="sidebar__brand">
		<span class="sidebar__logo">CA</span>
		<div>
			<p class="sidebar__title">Cinema</p>
			<p class="sidebar__subtitle">Ticketing System</p>
		</div>
	</div>

	<div class="sidebar__user">
		<p class="sidebar__label">Connected as</p>
		<p class="sidebar__name"><?php echo htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8'); ?></p>
	</div>

	<nav class="sidebar__nav" aria-label="Main navigation">
        <a href="/Cinema/dashboard" class="sidebar__link<?= getActive('/Cinema/dashboard', $currentUri) ?>">Dashboard</a>
        
        <?php if ($isAdmin): ?>
            <a href="/Cinema/films" class="sidebar__link<?= getActive('/Cinema/films', $currentUri) ?>">Manage Films</a>
            <a href="/Cinema/rooms" class="sidebar__link<?= getActive('/Cinema/rooms', $currentUri) ?>">Manage Rooms</a>
            <a href="/Cinema/schedules" class="sidebar__link<?= getActive('/Cinema/schedules', $currentUri) ?>">Manage Sessions</a>
        <?php else: ?>
            <a href="/Cinema/films" class="sidebar__link<?= getActive('/Cinema/films', $currentUri) ?>">Now Playing</a>
            <a href="/Cinema/schedules" class="sidebar__link<?= getActive('/Cinema/schedules', $currentUri) ?>">Sessions</a>
            <?php if ($isLoggedIn): ?>
                <a href="/Cinema/reservations" class="sidebar__link<?= getActive('/Cinema/reservations', $currentUri) ?>">My Reservations</a>
            <?php endif; ?>
        <?php endif; ?>
	</nav>

	<?php if ($isLoggedIn): ?>
		<div class="sidebar__section">
			<a href="/Cinema/logout" class="sidebar__link" style="color: #f87171;">Logout</a>
		</div>
	<?php endif; ?>
</aside>
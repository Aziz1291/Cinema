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
    if ($path === '/Cinema/dashboard' && ($uri === '/Cinema/dashboard' || $uri === '/Cinema' || $uri === '/Cinema/')) {
        return ' sidebar__link--active';
    }
    if ($path !== '/Cinema/dashboard' && strpos((string)$uri, $path) === 0) {
        return ' sidebar__link--active';
    }
    return '';
}
?>

<a href="#main-content" class="skip-link">Skip to main content</a>
<aside class="sidebar" id="appSidebar" aria-label="Main Navigation">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <a href="/Cinema/dashboard" class="sidebar__brand">
            <span class="sidebar__logo" aria-hidden="true">CA</span>
            <div>
                <h1 class="sidebar__title">Cinema</h1>
                <p class="sidebar__subtitle">Ticketing System</p>
            </div>
        </a>
        <button type="button" class="sidebar__close-btn" aria-label="Close Navigation" onclick="document.getElementById('appSidebar').classList.remove('is-open'); document.getElementById('menuTrigger').setAttribute('aria-expanded', 'false'); document.getElementById('menuTrigger').focus()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="sidebar__user-badge">
        <span class="sidebar__user-role"><?php echo $isAdmin ? 'Admin Console' : ($isLoggedIn ? 'Customer Portal' : 'Guest Visitor'); ?></span>
        <span class="sidebar__user-name"><?php echo htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <nav class="sidebar__nav">
        <span class="sidebar__section-title">Navigation</span>
        <a href="/Cinema/dashboard" class="sidebar__link<?= getActive('/Cinema/dashboard', $currentUri) ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            <span>Dashboard</span>
        </a>

        <?php if ($isAdmin): ?>
            <span class="sidebar__section-title" style="margin-top:12px;">Management</span>
            <a href="/Cinema/films" class="sidebar__link<?= getActive('/Cinema/films', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
                <span>Manage Films</span>
            </a>
            <a href="/Cinema/rooms" class="sidebar__link<?= getActive('/Cinema/rooms', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Manage Rooms</span>
            </a>
            <a href="/Cinema/schedules" class="sidebar__link<?= getActive('/Cinema/schedules', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Manage Sessions</span>
            </a>
            <a href="/Cinema/reservations" class="sidebar__link<?= getActive('/Cinema/reservations', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2"/></svg>
                <span>Customer Bookings</span>
            </a>
        <?php else: ?>
            <a href="/Cinema/films" class="sidebar__link<?= getActive('/Cinema/films', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                <span>Now Playing</span>
            </a>
            <a href="/Cinema/schedules" class="sidebar__link<?= getActive('/Cinema/schedules', $currentUri) ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Sessions & Times</span>
            </a>
            <?php if ($isLoggedIn): ?>
                <a href="/Cinema/reservations" class="sidebar__link<?= getActive('/Cinema/reservations', $currentUri) ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2"/></svg>
                    <span>My Reservations</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar__footer">
        <a href="/Cinema/privacy" class="sidebar__link">Privacy and cookies</a>
        <?php if ($isLoggedIn): ?>
            <a href="/Cinema/logout" class="sidebar__link" style="color: var(--cinema-ruby);" onclick="this.nextElementSibling.submit(); return false;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Sign Out</span>
            </a><form method="post" action="/Cinema/logout" hidden><?= csrf_field() ?></form>
        <?php else: ?>
            <a href="/Cinema/login" class="sidebar__link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Sign In</span>
            </a>
        <?php endif; ?>
    </div>
</aside>

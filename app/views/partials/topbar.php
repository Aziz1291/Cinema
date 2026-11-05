<?php
$displayName  = 'Guest';
$sessionLabel = 'Guest';
$avatarLetter = 'G';
$actionLabel  = 'Sign In';
$actionHref   = '/Cinema/login';
$isLoggedIn   = false;

if (isset($currentUser) && $currentUser) {
    $firstName    = $currentUser->getFirstName();
    $username     = $currentUser->getUsername();
    $displayName  = $firstName ? $firstName : $username;
    $sessionLabel = ucfirst((string) $currentUser->getRole());
    $avatarSource = $firstName ? $firstName : $username;
    $avatarLetter = strtoupper(substr((string) $avatarSource, 0, 1));
    $actionLabel  = 'Sign Out';
    $actionHref   = '/Cinema/logout';
    $isLoggedIn   = true;
}
?>
<header class="topbar">
    <div class="topbar__left">
        <button type="button" class="menu-trigger" id="menuTrigger" aria-label="Toggle navigation menu" onclick="document.getElementById('appSidebar').classList.toggle('is-open')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar__breadcrumb">
            <span>Cinema</span> / <span style="color:var(--ink); font-weight:700;"><?php echo htmlspecialchars((string)$sessionLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <form class="topbar__search-form" action="/Cinema/films" method="GET">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--muted); margin-right:8px;" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" class="topbar__search-input" placeholder="Quick search films..." aria-label="Quick search films">
    </form>

    <div class="topbar__actions">
        <div class="topbar__profile">
            <div class="topbar__avatar" aria-hidden="true">
                <?php echo htmlspecialchars((string) $avatarLetter, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <span class="topbar__profile-name"><?php echo htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <a href="<?php echo htmlspecialchars((string) $actionHref, ENT_QUOTES, 'UTF-8'); ?>" class="btn <?php echo $isLoggedIn ? 'btn--secondary btn--sm' : 'btn--primary btn--sm'; ?>">
            <?php if ($isLoggedIn): ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Logout</span>
            <?php else: ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Login</span>
            <?php endif; ?>
        </a>
    </div>
</header>

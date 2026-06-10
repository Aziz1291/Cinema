<?php
$displayName  = 'Guest';
$sessionLabel = 'Guest';
$avatarLetter = 'G';
$actionLabel  = 'Login';
$actionHref   = '/Cinema/login';
$isLoggedIn   = false;

if (isset($currentUser) && $currentUser) {
	$firstName    = $currentUser->getFirstName();
	$username     = $currentUser->getUsername();
	$displayName  = $firstName ? $firstName : $username;
	$sessionLabel = ucfirst((string) $currentUser->getRole());
	$avatarSource = $firstName ? $firstName : $username;
	$avatarLetter = strtoupper(substr((string) $avatarSource, 0, 1));
	$actionLabel  = 'Logout';
	$actionHref   = '/Cinema/logout';
	$isLoggedIn   = true;
}
?>
<header class="topbar">

	<div class="topbar__actions" style="margin-left: auto;">

		<div class="topbar__profile">
			<div class="topbar__avatar" aria-hidden="true">
				<?php echo htmlspecialchars((string) $avatarLetter, ENT_QUOTES, 'UTF-8'); ?>
			</div>
			<div class="topbar__profile-copy">
				<p class="topbar__profile-name"><?php echo htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8'); ?></p>
				<p class="topbar__profile-role"><?php echo htmlspecialchars((string) $sessionLabel, ENT_QUOTES, 'UTF-8'); ?></p>
			</div>
		</div>

		<a href="<?php echo htmlspecialchars((string) $actionHref, ENT_QUOTES, 'UTF-8'); ?>" class="topbar__action <?php echo $isLoggedIn ? 'topbar__action--logout' : 'topbar__action--login'; ?>">
			<?php if ($isLoggedIn): ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
				</svg>
				<span>Logout</span>
			<?php else: ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
				</svg>
				<span>Login</span>
			<?php endif; ?>
		</a>

	</div>
</header>

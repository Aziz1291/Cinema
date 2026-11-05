<?php $showAcknowledgement = !empty($showAcknowledgement); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($showAcknowledgement && $path === '/Cinema'): ?>
    <meta name="description" content="Explore an academic cinema ticketing demo with screenings, seat selection and an administration dashboard.">
    <link rel="canonical" href="https://azizkouki.com/projects/cinema">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Cinema Ticketing | Aziz Kouki">
    <meta property="og:description" content="Explore an academic cinema ticketing demo with screenings, seat selection and an administration dashboard.">
    <meta property="og:url" content="https://azizkouki.com/projects/cinema">
    <meta property="og:image" content="https://azizkouki.com/work/cinema/admin.webp">
    <meta property="og:image:alt" content="Cinema ticketing demo management dashboard">
    <meta name="twitter:card" content="summary_large_image">
    <title>Cinema Ticketing Demo | Aziz Kouki</title>
    <?php else: ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Privacy and essential cookie information for the Cinema demonstration application.">
    <title>Privacy notice | Cinema demo</title>
    <?php endif; ?>
    <style>
        :root { color-scheme: dark; font-family: system-ui, sans-serif; background: #101017; color: #f4f1f2; }
        body { margin: 0; line-height: 1.65; }
        main { max-width: 48rem; margin: 3rem auto; padding: 0 1.25rem 3rem; }
        h1, h2 { line-height: 1.2; } h1 { font-size: clamp(2rem, 6vw, 3rem); }
        h2 { font-size: 1.25rem; margin-top: 2rem; } a { color: #ffb4c4; }
        .notice { background: #241c25; border: 1px solid #7e4d5d; border-radius: .75rem; padding: 1rem 1.25rem; }
        button { background: #e75b7b; color: #160d12; border: 0; border-radius: .5rem; padding: .8rem 1.2rem; font: inherit; font-weight: 700; cursor: pointer; }
        :focus-visible { outline: 3px solid #ffd3dd; outline-offset: 4px; }
    </style>
</head>
<body><main>
    <p>CINEMA DEMO</p>
    <h1>Privacy and cookies</h1>
    <div class="notice"><p>This is an academic demonstration. Avoid entering sensitive or real personal information, and do not reuse a password.</p>
    <p>To use the demo, please read and acknowledge this notice. A session cookie is used to operate the site.</p></div>
    <h2>Information used by the demo</h2>
    <p>Account registration asks for a username, first and last name, email address, date of birth and password. The application stores a password hash. If you make a reservation, it stores the account, selected seat and screening. Administrators can view reservation and account details shown in the administration interface.</p>
    <p>The application uses this information to create accounts, sign users in, manage screenings and show reservations. Administrators may also upload film poster images.</p>
    <h2>Cookies and session security</h2>
    <p>The application uses a PHP session cookie to maintain your session, remember this acknowledgement and support security checks on forms. The session cookie is set when this notice is loaded. The application code does not configure analytics or advertising cookies. Clearing the session cookie ends your session and you will be asked to acknowledge the notice again.</p>
    <h2>Hosting and requests</h2>
    <p>The application is hosted on Render and uses an Aiven-hosted database. The interface loads fonts from Google Fonts, which receives requests from your browser. Hosting and network providers may process technical request information, such as IP addresses and server logs, as part of operating their services. Their practices and retention periods are governed by their own policies.</p>
    <h2>Retention and contact</h2>
    <p>Information submitted to the demo can remain in its database; there is no automatic deletion period specified in the application. For questions or a request concerning demo data, contact <a href="mailto:aziz@azizkouki.com">aziz@azizkouki.com</a>.</p>
    <?php if ($showAcknowledgement): ?>
    <form action="/Cinema/acknowledge-privacy" method="post">
        <?= csrf_field() ?>
        <button type="submit">I have read the notice — open the demo</button>
    </form>
    <p><a href="https://azizkouki.com/">Leave the demo</a></p>
    <?php else: ?>
    <p><a href="/Cinema/">Return to the demo</a></p>
    <?php endif; ?>
</main></body></html>

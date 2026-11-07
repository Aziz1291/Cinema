<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if ($path === '/Cinema'): ?>
  <title>Cinema Ticketing Demo | Aziz Kouki</title>
  <meta name="description" content="Explore an academic cinema ticketing demo with screenings, seat selection and an administration dashboard.">
  <link rel="canonical" href="https://azizkouki.com/projects/cinema">
  <meta property="og:type" content="website"><meta property="og:title" content="Cinema Ticketing | Aziz Kouki">
  <meta property="og:description" content="Explore an academic cinema ticketing demo with screenings, seat selection and an administration dashboard.">
  <meta property="og:url" content="https://azizkouki.com/projects/cinema"><meta property="og:image" content="https://azizkouki.com/work/cinema/admin.webp">
  <meta property="og:image:alt" content="Cinema ticketing demo management dashboard"><meta name="twitter:card" content="summary_large_image">
  <?php else: ?><meta name="robots" content="noindex, nofollow"><title>Privacy notice | Cinema demo</title><?php endif; ?>
  <style>
    *{box-sizing:border-box} body{margin:0;min-height:100vh;display:flex;flex-direction:column;font:16px/1.55 system-ui,sans-serif;color:#172033;background:#eceff4}
    .backdrop{flex:1;background:rgba(15,23,42,.52);display:grid;place-items:center;padding:20px}
    .card{background:#fff;border-radius:16px;max-width:480px;width:100%;padding:28px;box-shadow:0 18px 60px #0f172a33}
    h1{font-size:1.5rem;line-height:1.2;margin:0 0 12px} p{margin:0 0 16px} a{color:#2563eb} label{display:flex;align-items:flex-start;gap:10px;margin:20px 0}
    input{margin-top:5px;accent-color:#2563eb} button{font:inherit;cursor:pointer} .primary{background:#2563eb;color:white;border:0;border-radius:8px;padding:10px 16px;font-weight:650}
    .cookie{background:#fff;box-shadow:0 -4px 20px #0f172a1a;padding:14px 20px;display:flex;align-items:center;gap:16px;justify-content:center;font-size:.9rem}
    .cookie p{margin:0;max-width:850px}.cookie button{border:1px solid #94a3b8;background:white;border-radius:7px;padding:6px 12px;white-space:nowrap}
    .cookie[hidden]{display:none}
    :focus-visible{outline:3px solid #2563eb;outline-offset:3px}@media(max-width:480px){.cookie{align-items:flex-start;flex-wrap:wrap}}
  </style>
</head>
<body>
<main class="backdrop"><section class="card" role="dialog" aria-modal="true" aria-labelledby="notice-title">
  <h1 id="notice-title">Before you explore Cinema</h1>
  <p>This academic demo uses shared data. Avoid real personal information and reused passwords.</p>
  <?php if (!empty($gateError)): ?><p role="alert" style="background:#fee2e2;color:#7f1d1d;border-radius:8px;padding:10px 12px"><?= htmlspecialchars($gateError, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
  <form action="/Cinema/acknowledge-privacy" method="post">
    <?= csrf_field() ?>
    <label><input type="checkbox" name="privacy_acknowledgement" value="yes" required autofocus><span>I have read the <a href="/Cinema/privacy" target="_blank" rel="noopener">privacy notice</a>.</span></label>
    <button class="primary" type="submit">Continue to demo</button>
  </form>
  <p style="margin:16px 0 0"><a href="https://azizkouki.com/">Leave the demo</a></p>
</section></main>
<aside class="cookie" aria-label="Cookie notice"><p>This demo uses a necessary PHP session cookie for sign-in, security and this acknowledgement. It is set when this page loads. <a href="/Cinema/privacy" target="_blank" rel="noopener">Learn more</a>.</p><button type="button" onclick="this.parentElement.hidden=true">Accept necessary cookies</button></aside>
</body></html>

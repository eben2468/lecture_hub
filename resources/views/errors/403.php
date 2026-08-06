<?php
/**
 * Nadics LectureHub — 403 Forbidden Error Page
 */
$code    = $code ?? 403;
$message = $message ?? 'You do not have permission to access this resource.';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0F172A 0%,#3B1F2B 50%,#7C2D12 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;color:#E2E8F0;overflow:hidden}
        .container{text-align:center;padding:40px;animation:fadeInUp .6s ease-out}
        .error-code{font-size:10rem;font-weight:900;line-height:1;background:linear-gradient(135deg,#EF4444,#F59E0B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .error-message{font-size:1.25rem;color:#94A3B8;margin:16px 0 40px;max-width:480px;margin-left:auto;margin-right:auto}
        .btn-home{display:inline-flex;align-items:center;gap:10px;padding:14px 32px;background:linear-gradient(135deg,#EF4444,#DC2626);color:#fff;border:none;border-radius:12px;font-size:.95rem;font-weight:600;cursor:pointer;text-decoration:none;transition:all .25s;box-shadow:0 8px 25px rgba(239,68,68,.35)}
        .btn-home:hover{transform:translateY(-2px);box-shadow:0 12px 35px rgba(239,68,68,.5);color:#fff}
        .shield{font-size:5rem;margin-bottom:20px;opacity:.6}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body>
    <div class="container">
        <div class="shield">🛡️</div>
        <div class="error-code"><?= (int)$code ?></div>
        <h1 style="font-size:1.75rem;font-weight:700;margin-bottom:8px;">Access Denied</h1>
        <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <a href="<?= isset($app_url) ? $app_url : '/' ?>" class="btn-home">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Go Back
        </a>
    </div>
</body>
</html>

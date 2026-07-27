<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized — BU-GSO LINKod</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .box { text-align: center; background: #fff; border-radius: 16px; padding: 48px 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 380px; }
        .code { font-size: 72px; font-weight: 800; color: #1a3c8f; line-height: 1; }
        h2 { font-size: 20px; font-weight: 700; margin: 12px 0 8px; color: #111; }
        p { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
        a { display: inline-block; padding: 12px 28px; background: #1a3c8f; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">403</div>
        <h2>Access Denied</h2>
        <p>You don't have permission to view this page.</p>
        <a href="{{ url('/') }}">Go Back Home</a>
    </div>
</body>
</html>

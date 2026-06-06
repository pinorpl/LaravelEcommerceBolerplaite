<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome!</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1a1a2e; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; color: #333; line-height: 1.6; }
        .cta { display: inline-block; margin: 24px 0; padding: 12px 28px; background: #e94560; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .footer { background: #f4f4f4; text-align: center; padding: 16px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛒 Ecommerce Boilerplate</h1>
    </div>
    <div class="body">
        <h2>Welcome, {{ $user->name }}!</h2>
        <p>
            Your account has been successfully created. You can now browse our product catalogue,
            add items to your cart and place orders.
        </p>
        <a class="cta" href="{{ config('app.url') }}">Start Shopping</a>
        <p>If you have any questions, feel free to reply to this email.</p>
        <p>Happy shopping,<br><strong>The Ecommerce Boilerplate Team</strong></p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Ecommerce Boilerplate. All rights reserved.
    </div>
</div>
</body>
</html>

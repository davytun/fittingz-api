<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirm Unsubscribe — Fittingz</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      padding: 48px;
      max-width: 480px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    h1 {
      color: #0f4c75;
      font-size: 24px;
      margin: 0 0 16px;
    }

    p {
      color: #555;
      font-size: 15px;
      line-height: 1.6;
      margin: 0 0 32px;
    }

    .button-group {
      display: flex;
      gap: 12px;
      justify-content: center;
    }

    button,
    a {
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 15px;
      cursor: pointer;
      border: none;
      transition: background 0.2s;
      font-family: inherit;
    }

    .btn-primary {
      background: #0f4c75;
      color: #fff;
    }

    .btn-primary:hover {
      background: #0a3a5a;
    }

    .btn-secondary {
      background: #e8e8e8;
      color: #333;
    }

    .btn-secondary:hover {
      background: #d8d8d8;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Unsubscribe from Fittingz emails?</h1>
    <p>You will no longer receive email notifications from Fittingz. You can re-enable them anytime in your account
      settings.</p>

    <form method="POST" action="{{ $signedUrl }}" style="margin: 0;">
      @csrf
      <div class="button-group">
        <button type="submit" class="btn-primary">Yes, unsubscribe</button>
        <a href="/" class="btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</body>

</html>
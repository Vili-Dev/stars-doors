# Mailer Setup (PHPMailer)

Stars Doors uses a simple `mailer.php` helper that prefers PHPMailer when present, and falls back to PHP's native `mail()`.

## Folder structure
- Place PHPMailer source files here: `includes/PHPMailer/src/`
  - Required files: `PHPMailer.php`, `SMTP.php`, `Exception.php`

The helper auto-detects PHPMailer at `includes/PHPMailer/src` and uses SMTP.

## Configuration
Set mail environment values in `config/.env` (copy from `.env.example` if needed):

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_account@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@starsdoors.com
MAIL_FROM_NAME=Stars Doors
```

Notes:
- For Gmail, create an App Password and use `tls` on port 587.
- In development, the helper relaxes TLS certificate checks for convenience.

## Usage
Include the helper and call `send_email()`:

```php
require_once __DIR__ . '/mailer.php';

$ok = send_email('user@example.com', 'Subject', '<p>HTML body</p>', true);
if (!$ok) {
    // handle failure
}
```

No direct `$mail` object usage is required; the helper selects PHPMailer automatically when available.
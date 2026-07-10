# Pentecost University Scholarship Management System

PUSMS is a Laravel 13 and Filament 5 application for managing Pentecost University scholarship beneficiaries, programmes, sponsors, renewals, documents, communications, reporting, users, roles, permissions, and audit trails.

## Stack

- PHP 8.3 or newer
- Laravel 13
- Filament 5
- Livewire 4
- MySQL
- Laravel queues, Redis-ready
- Laravel Mail and Notifications
- Spatie Laravel Permission
- Laravel HTTP Client for SMS provider integration

## Local Setup

Install dependencies:

```bash
php composer.phar install
npm install
```

Create the environment file and key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure MySQL in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pusms
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

## Administrator Setup

Set these values in `.env` before seeding:

```dotenv
PUSMS_ADMIN_NAME="Super Administrator"
PUSMS_ADMIN_EMAIL="admin@example.com"
PUSMS_ADMIN_PASSWORD="use-a-long-random-password"
```

Then run:

```bash
php artisan db:seed
```

The default admin is only created when the email and password are explicitly provided. Do not commit `.env`.

## Admin Panel

The Filament admin panel is available at:

```text
/admin
```

Panel access requires an active user with one of these roles:

- Super Administrator
- Scholarship Secretary
- Committee Chairman
- Committee Member
- Read-Only Officer

## Queues

The default local queue connection is database-backed. For production, use Redis:

```dotenv
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Run a queue worker:

```bash
php artisan queue:work --tries=3
```

## Transactional Email and SMS

PUSMS sends scholarship communication through provider-independent notification services:

- `App\Services\Notifications\Contracts\EmailSender`
- `App\Services\Notifications\Contracts\SmsSender`

The current production adapters are Laravel SMTP email and Hubtel SMS. Business logic, including the Communication History compose flow, depends on these interfaces instead of provider SDKs directly. Delivery attempts are stored against communication recipients with status, provider, provider message ID where available, attempt result data, and failure reason.

### Email Provider Setup

Configure SMTP or a compatible mail relay in `.env`:

```dotenv
PUSMS_EMAIL_PROVIDER=smtp
PUSMS_EMAIL_TIMEOUT=30
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

For Gmail, `MAIL_PASSWORD` must be a Gmail app password, not the normal account password. Verify the sender account/domain in the provider dashboard before production use.

### SMS Provider Setup

PUSMS uses Hubtel through `App\Services\Notifications\Senders\HubtelSmsSender`. Configure:

```dotenv
PUSMS_SMS_PROVIDER=hubtel
PUSMS_SMS_DEFAULT_COUNTRY_CODE=233
PUSMS_SMS_MAX_LENGTH=918
PUSMS_SMS_TIMEOUT=30
HUBTEL_CLIENT_ID=
HUBTEL_CLIENT_SECRET=
HUBTEL_SENDER_ID=PUSMS
HUBTEL_BASE_URL=https://smsc.hubtel.com/v1/messages/send
```

`PUSMS_SMS_DEFAULT_COUNTRY_CODE` lets local Ghana numbers such as `0244123456` normalize to E.164. For non-Ghana numbers, store the phone number with an explicit country code such as `+234...` or `+1...`.

Never place provider credentials in source code, screenshots, fixtures, or documentation.

### Fake Providers and Safe Test Commands

Automated tests use fake providers or mocked HTTP/Mail, so they do not send real messages. For local fake delivery, set:

```dotenv
PUSMS_EMAIL_PROVIDER=fake
PUSMS_SMS_PROVIDER=fake
```

Development-only test commands are available outside production:

```bash
php artisan pusms:test-email user@example.com
php artisan pusms:test-sms +233244123456
```

Both commands use the same `EmailSender` and `SmsSender` services as the application and are disabled when `APP_ENV=production`.

### Calling the Notification Services

```php
use App\Services\Notifications\Contracts\EmailSender;
use App\Services\Notifications\Data\EmailMessage;

app(EmailSender::class)->send(new EmailMessage(
    to: 'student@example.com',
    subject: 'Scholarship update',
    text: 'Your scholarship record is ready.',
));
```

```php
use App\Services\Notifications\Contracts\SmsSender;
use App\Services\Notifications\Data\SmsMessage;

app(SmsSender::class)->send(new SmsMessage(
    to: '+233244123456',
    message: 'Your scholarship record is ready.',
));
```

Provider responses are normalized into `NotificationResult`. Validation, configuration, transient provider, and permanent provider failures use domain-specific exceptions under `App\Services\Notifications\Exceptions`.

## File Storage

Student documents must be stored on a private disk and served only through authorized routes. Do not expose private uploads directly from public paths.

## Scheduler

Add the Laravel scheduler to the server cron:

```bash
* * * * * php /path/to/pusms/artisan schedule:run >> /dev/null 2>&1
```

## Tests

Run tests with:

```bash
php artisan test
```

Critical tests should cover student creation, duplicate student IDs, scholarship assignment, duplicate scholarship prevention, history preservation, renewal transitions, authorization, recipient snapshots, template variables, queued email and SMS dispatch, mocked SMS HTTP responses, and failed communication handling.

## Deployment Checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a strong `APP_KEY`.
- Configure MySQL backups and least-privilege database credentials.
- Use HTTPS and secure session settings.
- Configure Redis queues and a process supervisor.
- Configure mail and Hubtel credentials through environment variables.
- Run `php artisan config:cache`, `route:cache`, and `view:cache`.
- Ensure storage permissions are correct and private documents are not publicly exposed.
- Run migrations in a controlled deployment step.
- Review user roles, permissions, and audit log retention.

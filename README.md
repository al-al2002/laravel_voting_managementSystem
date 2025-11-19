# VoteMaster Voting Management

[![GitHub Actions status](https://github.com/laravel/framework/actions/workflows/tests.yml/badge.svg)](https://github.com/laravel/framework/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/framework)
[![Packagist Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/framework)
[![Packagist License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/framework)

## Overview

VoteMaster is a Laravel-based voting management system featuring voters, elections, a communication inbox, and a password reset flow. Key capabilities include:

- Admin dashboards for elections, candidates, voters, and live monitoring.
- User dashboards with profile controls, voting history, and live monitors.
- A resilient AJAX inbox with image attachments, optimistic updates, and unread badges.
- A secure forgot-password flow that emails a 6-digit verification code.

## Getting Started

1. Copy `.env.example` to `.env` and configure your database and queue settings.
2. Run `composer install`, `npm install`, and `php artisan key:generate`.
3. Run the migrations with `php artisan migrate`.
4. Launch the app via `php artisan serve` or your preferred local server.

## Email Delivery (Gmail)

The password reset flow relies on email delivery. In local development it defaults to the `log` mailer, but to send codes to Gmail users configure SMTP:

1. Create a Gmail [App Password](https://support.google.com/accounts/answer/185833) for the sending account.
2. Add these overrides to your `.env`:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your@gmail.com
   MAIL_PASSWORD=<your app password>
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="hello@votemaster.com"
   MAIL_FROM_NAME="VoteMaster"
   ```

3. Run `php artisan config:clear` and `php artisan cache:clear` so Laravel picks up the new mail settings.
4. Submit the “Forgot password” form and verify the 6-digit code lands in the recipient Gmail inbox.

If delivery still fails, review `storage/logs/laravel.log` for SMTP errors and compare timestamps with [Gmail’s security checkup](https://myaccount.google.com/security-checkup).

## Contributing

Contributions are welcome—refer to the [Laravel contribution guide](https://laravel.com/docs/contributions) for standards and Git workflow.

## Security

Report vulnerabilities via email to [taylor@laravel.com](mailto:taylor@laravel.com).

## License

This project is released under the [MIT license](https://opensource.org/licenses/MIT).

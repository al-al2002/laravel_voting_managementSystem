<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCode;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Testing email to: {$email}");
        $this->info("MAIL_MAILER: " . config('mail.default'));
        $this->info("MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->info("MAIL_USERNAME: " . config('mail.mailers.smtp.username'));
        $this->info("MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
        $this->info("MAIL_FROM: " . config('mail.from.address'));

        try {
            Mail::to($email)->send(new PasswordResetCode('TEST123'));
            $this->info("✓ Email sent successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send email:");
            $this->error($e->getMessage());
            return 1;
        }
    }
}


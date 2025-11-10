<?php

namespace App\Console\Commands;

use App\Models\DevMagicLink;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateDevMagicLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:magic-link {email : The email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a magic link for development authentication (non-production only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('This command cannot be used in production environment.');
            $this->error('Magic links are a development-only feature for testing purposes.');
            return Command::FAILURE;
        }

        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $expiresInMinutes = 60;
        $magicLink = DevMagicLink::generateForUser($user, $expiresInMinutes);

        $this->newLine();
        $this->info("Magic link generated successfully for: {$user->email}");
        $this->info("Expires in: {$expiresInMinutes} minutes");
        $this->newLine();
        $this->line("Magic Link URL:");
        $this->line($magicLink->getUrl());
        $this->newLine();

        return Command::SUCCESS;
    }
}

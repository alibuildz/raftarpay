<?php

namespace RaftarPay\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'raftarpay:install {--force : Overwrite any existing files}';

    protected $description = 'Publish RaftarPay config & migration, then run the migration.';

    public function handle(): int
    {
        $this->info('Installing RaftarPay…');

        $this->call('vendor:publish', [
            '--provider' => 'RaftarPay\\RaftarPayServiceProvider',
            '--tag'      => 'raftarpay-config',
            '--force'    => (bool) $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'RaftarPay\\RaftarPayServiceProvider',
            '--tag'      => 'raftarpay-migrations',
            '--force'    => (bool) $this->option('force'),
        ]);

        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        $this->newLine();
        $this->info('RaftarPay is ready. Add your gateway credentials to .env:');
        $this->line('  JAZZCASH_MERCHANT_ID, JAZZCASH_PASSWORD, JAZZCASH_INTEGRITY_SALT');
        $this->line('  EASYPAISA_STORE_ID, EASYPAISA_HASH_KEY');

        $this->newLine();
        $this->line('  ─────────────────────────────────────────────');
        $this->line('  💚 Enjoying RaftarPay? Built by Muhammad Ali.');
        $this->line('     Need a Laravel dev / payment integration?');
        $this->line('     LinkedIn: https://www.linkedin.com/in/alibuilds/');
        $this->line('     Upwork:   https://www.upwork.com/freelancers/~01a2c3edded808759c');
        $this->line('     ⭐ Star:  https://github.com/alibuildz/raftarpay');
        $this->line('  ─────────────────────────────────────────────');

        return self::SUCCESS;
    }
}

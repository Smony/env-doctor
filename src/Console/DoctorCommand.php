<?php

namespace Smony\EnvDoctor\Laravel;

use Illuminate\Console\Command;
use Smony\EnvDoctor\EnvReader;
use Smony\EnvDoctor\EnvScanner;

class DoctorCommand extends Command
{
    protected $signature = 'doctor:env';
    protected $description = 'Check .env for issues';

    public function handle(): int
    {
        $reader = new EnvReader();
        $scanner = new EnvScanner();

        $env = $reader->read();
        $used = $scanner->scan();

        $missing = [];
        $unused = [];
        $dangerous = [];

        foreach ($used as $key) {
            if (!isset($env[$key])) {
                $missing[] = $key;
            }
        }

        foreach ($env as $key => $value) {
            if (!in_array($key, $used)) {
                $unused[] = $key;
            }
        }

        if (
            isset($env['APP_ENV']) &&
            strtolower($env['APP_ENV']) === 'production' &&
            isset($env['APP_DEBUG']) &&
            in_array(strtolower($env['APP_DEBUG']), ['true', '1'])
        ) {
            $dangerous[] = 'APP_DEBUG=true in production';
        }

        $this->newLine();
        $this->info('=== ENV DOCTOR REPORT ===');
        $this->newLine();

        if (!empty($missing)) {
            $this->error('❌ Missing:');
            foreach ($missing as $key) {
                $this->line("  - $key");
            }
            $this->newLine();
        }

        if (!empty($unused)) {
            $this->warn('⚠️ Unused:');
            foreach ($unused as $key) {
                $this->line("  - $key");
            }
            $this->newLine();
        }

        if (!empty($dangerous)) {
            $this->error('🚨 Dangerous:');
            foreach ($dangerous as $item) {
                $this->line("  - $item");
            }
            $this->newLine();
        }

        if (empty($missing) && empty($unused) && empty($dangerous)) {
            $this->info('✅ Everything looks good!');
            $this->newLine();
        }

        $this->info('Summary:');
        $this->line('  Missing: ' . count($missing));
        $this->line('  Unused: ' . count($unused));
        $this->line('  Dangerous: ' . count($dangerous));
        $this->newLine();

        return empty($missing) && empty($dangerous) ? 0 : 1;
    }
}
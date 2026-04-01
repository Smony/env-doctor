<?php

namespace Smony\EnvDoctor\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Smony\EnvDoctor\EnvReader;
use Smony\EnvDoctor\EnvScanner;

class CliDoctorCommand extends Command
{
    protected static $defaultName = 'doctor:env';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reader = new EnvReader();
        $scanner = new EnvScanner();

        $env = $reader->read();
        $used = $scanner->scan();

        $missing = [];
        $unused = [];

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

        $output->writeln('');
        $output->writeln('<info>=== ENV DOCTOR REPORT ===</info>');
        $output->writeln('');

        if (!empty($missing)) {
            $output->writeln('<error>❌ Missing:</error>');
            foreach ($missing as $key) {
                $output->writeln("  - $key");
            }
            $output->writeln('');
        }

        if (!empty($unused)) {
            $output->writeln('<comment>⚠️ Unused:</comment>');
            foreach ($unused as $key) {
                $output->writeln("  - $key");
            }
            $output->writeln('');
        }

        if (empty($missing) && empty($unused)) {
            $output->writeln('<info>✅ Everything looks good!</info>');
        }

        $dangerous = [];

        if (
            isset($env['APP_ENV']) &&
            strtolower($env['APP_ENV']) === 'production'
        ) {
            if (
                isset($env['APP_DEBUG']) &&
                in_array(strtolower($env['APP_DEBUG']), ['true', '1'])
            ) {
                $dangerous[] = 'APP_DEBUG=true in production';
            }
        }

        if (!empty($dangerous)) {
            $output->writeln('<error>🚨 Dangerous:</error>');
            foreach ($dangerous as $item) {
                $output->writeln("  - $item");
            }
            $output->writeln('');
        }

        // 📊 Summary
        $output->writeln('<info>Summary:</info>');
        $output->writeln('  Missing: ' . count($missing));
        $output->writeln('  Unused: ' . count($unused));
        $output->writeln('  Dangerous: ' . count($dangerous));
        $output->writeln('');

        return empty($missing) && empty($dangerous)
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
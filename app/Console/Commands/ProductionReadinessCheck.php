<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionReadinessCheck extends Command
{
    protected $signature = 'waterfall:production-check';

    protected $description = 'Validate production-critical application, queue, and Firebase configuration';

    public function handle(): int
    {
        $checks = [
            ['Production environment', app()->environment('production')],
            ['Debug mode disabled', ! (bool) config('app.debug')],
            ['HTTPS application URL', str_starts_with((string) config('app.url'), 'https://')],
            ['Asynchronous queue configured', ! in_array(config('queue.default'), ['sync', 'null'], true)],
            ['Firebase project configured', filled(config('services.firebase.project_id'))],
            ['Firebase credentials configured', filled(config('services.firebase.credentials'))],
            ['Firebase service-account file exists', is_file((string) config('services.firebase.credentials'))],
            ['Application key configured', filled(config('app.key'))],
        ];

        $failed = false;

        foreach ($checks as [$label, $passed]) {
            if ($passed) {
                $this->components->info($label);
            } else {
                $failed = true;
                $this->components->error($label);
            }
        }

        if ($failed) {
            $this->newLine();
            $this->error('Production readiness checks failed. Do not deploy yet.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Production configuration checks passed.');

        return self::SUCCESS;
    }
}

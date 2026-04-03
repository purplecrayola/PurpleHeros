<?php

namespace App\Console\Commands;

use App\Models\PayrollRun;
use App\Services\Payroll\PayrollRunGeneratorService;
use Illuminate\Console\Command;

class GeneratePayrollRun extends Command
{
    protected $signature = 'payroll:generate-run {run_id : The payroll run ID} {--user_id=* : Optional user IDs to generate for}';

    protected $description = 'Calculate payroll lines and payslips for a payroll run';

    public function handle(PayrollRunGeneratorService $generator): int
    {
        $run = PayrollRun::query()->find($this->argument('run_id'));

        if (! $run) {
            $this->error('Payroll run not found.');

            return self::FAILURE;
        }

        $userIds = $this->option('user_id');
        $result = $generator->generate($run, empty($userIds) ? null : $userIds);

        $this->info('Payroll run generated.');
        $this->line('Run ID: '.$result['run_id']);
        $this->line('Employees generated: '.$result['generated']);

        return self::SUCCESS;
    }
}

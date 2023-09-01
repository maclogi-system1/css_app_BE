<?php

namespace App\Console\Commands;

use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ExecuteScheduledMacros extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-scheduled-macros';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ready macros and execute them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->checkAndUpdateMacro();

        $macros = MacroConfiguration::where('status', MacroConstant::MACRO_STATUS_READY)
            ->whereIn('macro_type', MacroConstant::MACRO_SCHEDULABLE_TYPES)
            ->get();

        if ($macros->isEmpty()) {
            return Command::SUCCESS;
        }

        $oneTime = [];
        $cycle = [];

        foreach ($macros as $macro) {
            if ($macro->isOneTime()) {
                $oneTime[] = $macros->shift();
            } else {
                $cycle[] = $macros->shift();
            }
        }

        $this->handleMacroOneTime($oneTime);
        $this->handleCyclicalMacros($cycle);

        return Command::SUCCESS;
    }

    private function checkAndUpdateMacro()
    {
        MacroConfiguration::where('status', MacroConstant::MACRO_STATUS_NOT_READY)
            ->whereIn('macro_type', MacroConstant::MACRO_SCHEDULABLE_TYPES)
            ->whereRaw("JSON_EXTRACT(time_conditions, '$.applicable_date') >= '".now()->format('Y-m-d')."'")
            ->update([
                'status' => MacroConstant::MACRO_STATUS_READY,
            ]);
    }

    private function handleMacroOneTime(array $macros)
    {
        $now = now();

        foreach ($macros as $macro) {
            $timeCondition = Carbon::create($macro->time_condition_designation);

            if (
                $timeCondition->year == $now->year
                && $timeCondition->month == $now->month
                && $timeCondition->day == $now->day
            ) {
                $this->executeByMacroType($macro);
                $macro->status = MacroConstant::MACRO_STATUS_FINISH;
                $macro->save();
            }
        }
    }

    private function handleCyclicalMacros(array $macros)
    {
        foreach ($macros as $macro) {
            $cronExpression = $macro->cron_expression;

            if ($cronExpression->isDue()) {
                $this->executeByMacroType($macro);
            }
        }
    }

    private function executeByMacroType(MacroConfiguration $macro): void
    {
        switch ($macro->macro_type) {
            case MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION:
                $this->createSimulationPolicy($macro);
                break;
            case MacroConstant::MACRO_TYPE_POLICY_REGISTRATION:
                $this->createPolicy($macro);
                break;
        }

        $this->info($macro->name.' is executed.');
    }

    private function createSimulationPolicy(MacroConfiguration $macro)
    {
        //
    }

    private function createPolicy(MacroConfiguration $macro)
    {
        //
    }
}

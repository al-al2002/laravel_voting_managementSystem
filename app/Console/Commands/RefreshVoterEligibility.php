<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class RefreshVoterEligibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voters:refresh-eligibility {--clear-overrides : Clear all admin overrides}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh eligibility for all voters based on skipped elections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clearOverrides = $this->option('clear-overrides');

        $voters = User::all();

        $this->info("Processing {$voters->count()} voters...\n");

        foreach ($voters as $voter) {
            $skippedCount = $voter->skippedElectionsCount();
            $autoEligible = $voter->isAutoEligible();
            $hadOverride = $voter->eligibility_overridden;
            $wasFinalEligible = $voter->finalEligibility();

            if ($clearOverrides && $hadOverride) {
                $voter->eligibility_overridden = false;
                $voter->is_eligible = $autoEligible;
                $voter->save();
                $this->warn("  [{$voter->voter_id}] {$voter->name}: Cleared override, skipped {$skippedCount}, now " . ($autoEligible ? 'ELIGIBLE' : 'NOT ELIGIBLE'));
            } else {
                $voter->refreshEligibility();
                $voter->refresh(); // Reload from database
                $status = $voter->finalEligibility() ? 'ELIGIBLE' : 'NOT ELIGIBLE';
                $stillHasOverride = $voter->eligibility_overridden;

                if ($hadOverride && !$stillHasOverride) {
                    $this->warn("  [{$voter->voter_id}] {$voter->name}: Override AUTO-CLEARED, skipped {$skippedCount}, now {$status}");
                } elseif ($stillHasOverride) {
                    $this->line("  [{$voter->voter_id}] {$voter->name}: Skipped {$skippedCount}, {$status} (ADMIN OVERRIDE)");
                } else {
                    $this->line("  [{$voter->voter_id}] {$voter->name}: Skipped {$skippedCount}, {$status}");
                }
            }
        }

        $this->info("\n✓ Eligibility refresh complete!");

        return 0;
    }
}


<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'User not logged in.');
        }

        $now = Carbon::now('Asia/Manila');

        // Elections categories
        $activeElectionsList = Election::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        $upcomingElectionsList = Election::where('start_date', '>', $now)->get();

        $closedElectionsList = Election::where('end_date', '<', $now)->get();
        // Skipped elections: ended after registration but user didn’t vote
                $userVoteIds = Vote::where('user_id', $user->id)->pluck('election_id')->toArray();

                $skippedElectionsList = Election::where('end_date', '<', $now)
                    ->where('end_date', '>=', $user->created_at)
                    ->whereNotIn('id', $userVoteIds)
                    ->get();

        $skippedCount = $skippedElectionsList->count();

        // ✅ Respect Admin Overrides
        $userModel = User::find($user->id);

        if ($userModel && !$userModel->eligibility_overridden) {
            // Auto-update eligibility only if admin has not manually overridden it
            if ($skippedCount >= 5 && $userModel->is_eligible) {
                $userModel->update(['is_eligible' => false]);
            } elseif ($skippedCount < 5 && !$userModel->is_eligible) {
                $userModel->update(['is_eligible' => true]);
            }
        }

        // Reload user to reflect updated eligibility or admin override
        $user = $userModel->fresh();

 return view('user.dashboard', [
    'user' => $user,
    'activeElectionsList' => $activeElectionsList,
    'upcomingElectionsList' => $upcomingElectionsList,
    'closedElectionsList' => $closedElectionsList,
    'skippedElectionsList' => $skippedElectionsList,
    'activeElections' => $activeElectionsList->count(),
    'upcomingElections' => $upcomingElectionsList->count(),
    'closedElections' => $closedElectionsList->count(),
    'skippedElections' => $skippedCount,
    'userVotesCount' => $user->votes()->pluck('election_id')->unique()->count(),
]);

    }
}

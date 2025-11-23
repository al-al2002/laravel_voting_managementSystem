<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VoterController extends Controller
{
    // List voters with optional filter
    public function index(Request $request)
    {
        $voters = User::orderBy('created_at', 'desc')->get(); // All users are voters now

        // Refresh auto eligibility for all voters
        foreach ($voters as $voter) {
            $voter->refreshEligibility();
        }

        // Apply filter if selected
        if ($request->filter === 'eligible') {
            $voters = $voters->filter(fn($v) => $v->finalEligibility());
        } elseif ($request->filter === 'not_eligible') {
            $voters = $voters->filter(fn($v) => !$v->finalEligibility());
        }

        // Manual pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginated = new LengthAwarePaginator(
            $voters->forPage($page, $perPage),
            $voters->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.voters.index', ['voters' => $paginated]);
    }

    // Toggle eligibility (admin override)
    public function toggleEligibility($id)
    {
        $voter = User::findOrFail($id);

        // Flip current eligibility status
        $newStatus = !$voter->finalEligibility();

        // Save as admin override and track skip count
        $voter->is_eligible = $newStatus;
        $voter->eligibility_overridden = true;
        $voter->override_at_skip_count = $voter->skippedElectionsCount();
        $voter->save();

        $message = $newStatus ? 'Voter marked as eligible successfully!' : 'Voter marked as not eligible successfully!';
        return redirect()->back()->with('success', $message);
    }

    // Refresh all voters (call after deleting election or vote)
    public function refreshAllVotersEligibility()
    {
        $voters = User::all(); // All users are voters now
        foreach ($voters as $voter) {
            $voter->refreshEligibility();
        }

        return redirect()->back()->with('success', 'Voter eligibility refreshed!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
  public function index()
{
    $now = now();

    $activeList = Election::where('start_date', '<=', $now)->where('end_date', '>=', $now)->get();
    $closedList = Election::where('end_date', '<', $now)->get();
    $upcomingList = Election::where('start_date', '>', $now)->get();
    $voters = User::all(); // All users are voters now since admins are in separate table

    $activeElections = $activeList->count();
    $closedElections = $closedList->count();
    $upcomingElections = $upcomingList->count();
    $totalVoters = $voters->count();

    $chartData = Election::withCount('votes')->get();
    $chartLabels = $chartData->pluck('title');
    $chartVotes = $chartData->pluck('votes_count');

    return view('admin.dashboard', compact(
        'activeElections', 'closedElections', 'upcomingElections', 'totalVoters',
        'activeList', 'closedList', 'upcomingList', 'voters',
        'chartLabels', 'chartVotes', 'chartData'
    ));
}

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CandidateController extends Controller
{
    /**
     * Display a listing of elections with candidates.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', null);
        $now = Carbon::now();
        $query = Election::with('candidates');

        if ($status) {
            $query->when($status === 'active', fn($q) => $q->where('start_date', '<=', $now)->where('end_date', '>=', $now))
                  ->when($status === 'upcoming', fn($q) => $q->where('start_date', '>', $now))
                  ->when($status === 'closed', fn($q) => $q->where('end_date', '<', $now));
        }

         $elections = $query->orderBy('start_date', 'DESC')->get();
        return view('admin.candidates.index', compact('elections'));
    }

    /**
     * Show the form for creating a new candidate.
     */
    public function create()
    {
        $elections = Election::where('end_date', '>', Carbon::now())->get();
        return view('admin.candidates.create', compact('elections'));
    }

    /**
     * Store a single candidate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'position'    => 'required|string|max:255',
            'election_id' => 'required|exists:elections,id',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $election = Election::findOrFail($validated['election_id']);
        if (now()->gt($election->end_date)) {
            return back()->with('error', 'Cannot add candidate to a closed election.');
        }

        if ($request->hasFile('photo')) {
            try {
                $path = $request->file('photo')->store('candidates', 'supabase');
                $validated['photo'] = $path;
                Log::info('Photo uploaded to Supabase: ' . $path);
            } catch (\Exception $e) {
                Log::error('Failed to upload photo: ' . $e->getMessage());
                return back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
            }
        }

        Candidate::create($validated);

        return redirect()->route('admin.candidates.index')
                         ->with('success', 'Candidate added successfully!');
    }

    /**
     * Store multiple candidates at once.
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'election_id'           => 'required|exists:elections,id',
            'candidates.*.name'     => 'required|string|max:255',
            'candidates.*.position' => 'required|string|max:255',
            'candidates.*.photo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $election = Election::findOrFail($request->election_id);
        if (now()->gt($election->end_date)) {
            return back()->with('error', 'Cannot add candidates to a closed election.');
        }

        foreach ($request->candidates as $candidate) {
            $data = array_merge(
                $candidate,
                ['election_id' => $request->election_id],
                !empty($candidate['photo']) ? ['photo' => $candidate['photo']->store('candidates', 'supabase')] : []
            );
            Candidate::create($data);
        }

        return redirect()->route('admin.candidates.index')
                         ->with('success', 'Candidates added successfully!');
    }

    /**
     * Show the form for editing a candidate.
     */
    public function edit(Candidate $candidate)
    {
        $elections = Election::all();
        return view('admin.candidates.edit', compact('candidate', 'elections'));
    }

    /**
     * Update a candidate.
     */
    public function update(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'position'    => 'required|string|max:255',
            'election_id' => 'required|exists:elections,id',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $election = Election::findOrFail($validated['election_id']);
        if (now()->gt($election->end_date)) {
            return back()->with('error', 'Cannot assign candidate to a closed election.');
        }

        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                try {
                    Storage::disk('supabase')->delete($candidate->photo);
                } catch (\Exception $e) {
                    // File doesn't exist or deletion failed, continue
                }
            }
            $validated['photo'] = $request->file('photo')->store('candidates', 'supabase');
        }

        $candidate->update($validated);

        return redirect()->route('admin.candidates.index')
                         ->with('success', 'Candidate updated successfully!');
    }

    /**
     * Delete a candidate.
     */
    public function destroy(Candidate $candidate)
    {
        if ($candidate->photo) {
            try {
                Storage::disk('supabase')->delete($candidate->photo);
            } catch (\Exception $e) {
                // File doesn't exist or deletion failed, continue
            }
        }

        $candidate->delete();

        return redirect()->route('admin.candidates.index')
                         ->with('success', 'Candidate deleted successfully!');
    }
}

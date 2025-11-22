<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Vote;

class VoteController extends Controller
{
    public function downloadPDF($electionId)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Get votes for this user and this election
        $votes = Vote::where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->with(['election', 'candidate'])
            ->get();

        if ($votes->isEmpty()) {
            return redirect()->back()->with('error', 'You have no votes recorded for this election.');
        }

        // Embed the logo as base64 to avoid GD/Imagick requirement
        $logoPath = public_path('images/votemaster.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('user.votes.receipt', [
            'user' => $user,
            'votes' => $votes,
            'logoBase64' => $logoBase64,
        ]);

        // Generate PDF content
        $pdfContent = $pdf->output();
        $fileName = 'Vote_Receipt_' . $user->id . '_Election_' . $electionId . '.pdf';
        $filePath = 'receipts/' . $fileName;

        // Store PDF in Supabase
        try {
            Storage::disk('supabase')->put($filePath, $pdfContent);
            Log::info('Vote receipt PDF uploaded to Supabase', [
                'user_id' => $user->id,
                'election_id' => $electionId,
                'path' => $filePath
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload vote receipt PDF to Supabase', [
                'user_id' => $user->id,
                'election_id' => $electionId,
                'error' => $e->getMessage()
            ]);
        }

        // Return download response
        return response()->streamDownload(function() use ($pdfContent) {
            echo $pdfContent;
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

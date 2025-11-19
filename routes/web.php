<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ----------------------
// Controllers
// ----------------------
// Auth
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ElectionController as AdminElectionController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\VoterController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\LiveMonitorController;
use App\Http\Controllers\Admin\SmsController;

// User
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ElectionController as UserElectionController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\VoteHistoryController;
use App\Http\Controllers\User\ResultController as UserResultController;
use App\Http\Controllers\User\LiveMonitorController as UserLiveMonitorController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\VoteController;

// ----------------------
// Root Redirect
// ----------------------
Route::get('/', fn() => redirect()->route('login'));

// ----------------------
// Auth Routes
// ----------------------
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset (Forgot Password)
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/enter-code', [ForgotPasswordController::class, 'showEnterCodeForm'])->name('password.enter_code');
Route::post('/password/check-code', [ForgotPasswordController::class, 'checkCode'])->name('password.check_code');
Route::post('/password/resend', [ForgotPasswordController::class, 'resendCode'])->name('password.resend');
Route::get('/password/set-new', [ForgotPasswordController::class, 'showSetNewForm'])->name('password.set_new');
Route::post('/password/set-new', [ForgotPasswordController::class, 'updatePassword'])->name('password.set_new.update');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// ----------------------
// Admin Routes
// ----------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'isAdmin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('elections', AdminElectionController::class);
    Route::resource('candidates', CandidateController::class);
    Route::post('/candidates/store-multiple', [CandidateController::class, 'storeMultiple'])->name('candidates.storeMultiple');

    Route::get('/voters', [VoterController::class, 'index'])->name('voters.index');
    Route::patch('/voters/{id}/toggle', [VoterController::class, 'toggleEligibility'])->name('voters.toggle');

    Route::get('/results', [ResultController::class, 'index'])->name('results');

    Route::get('/live-monitor', [LiveMonitorController::class, 'index'])->name('live-monitor');
    Route::get('/live-monitor/data', [LiveMonitorController::class, 'data'])->name('live-monitor.data');

    // Admin SMS / Inbox
    Route::get('/sms', [SmsController::class, 'index'])->name('sms.index'); // Inbox (list of conversations)
    Route::get('/sms/conversation/{conversation_id}', [SmsController::class, 'conversation'])->name('sms.conversation'); // View conversation
    Route::post('/sms/reply/{conversation_id}', [SmsController::class, 'reply'])->name('sms.reply'); // Reply
    Route::patch('/sms/read/{id}', [SmsController::class, 'markAsRead'])->name('sms.read'); // Mark as read
    Route::delete('/sms/delete/{id}', [SmsController::class, 'destroy'])->name('sms.delete'); // Delete
    Route::delete('sms/conversation/{conversation_id}', [SmsController::class, 'destroyConversation'])->name('sms.destroyConversation');
});

// Admin unread count
Route::get('/admin/sms/unread-count', function () {
    return response()->json([
        'count' => \App\Models\Message::where('status', 'unread')
            ->where('sender_type', 'user')
            ->where('to', 'admin')
            ->count(),
    ]);
})->name('admin.sms.unread-count');

// ----------------------
// User Routes
// ----------------------
Route::prefix('user')->name('user.')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Elections
    Route::get('/elections', [UserElectionController::class, 'index'])->name('elections.index');
    Route::get('/elections/{election}', [UserElectionController::class, 'show'])->name('elections.show');
    Route::post('/elections/{election}/vote', [UserElectionController::class, 'vote'])->name('elections.vote');

    // Results
    Route::get('/results', [UserResultController::class, 'index'])->name('results.index');

    // Vote History
    Route::get('/votes/history', [VoteHistoryController::class, 'fetch'])->name('votes.history');

    // Live Monitor
    Route::get('/live-monitor', [UserLiveMonitorController::class, 'index'])->name('live-monitor.index');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/settings', fn() => view('user.profile.settings'))->name('profile.settings');

    // Password
    Route::get('/profile/password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::post('/profile/password/change', [PasswordController::class, 'update'])->name('password.update');

    //User / Inbox
    Route::prefix('sms')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index'); // Inbox list
        Route::get('/create', [MessageController::class, 'create'])->name('create'); // New message form
        Route::post('/', [MessageController::class, 'store'])->name('store'); // Send new
        Route::get('/conversation/{conversation_id}', [MessageController::class, 'conversation'])->name('conversation'); // View conversation
        Route::post('/reply/{conversation_id}', [MessageController::class, 'reply'])->name('reply'); // Reply in conversation
        Route::delete('/conversation/{conversation_id}', [MessageController::class, 'destroyConversation'])->name('destroyConversation'); // Permanently delete conversation for user
    });

    // Vote Receipt PDF
    Route::get('/vote/download-pdf/{election}', [VoteController::class, 'downloadPDF'])->name('vote.downloadPDF');
});

// User unread count
Route::get('/user/unread-count', function () {
    $count = \App\Models\Message::where('to', 'user')
        ->where('status', 'unread')
        ->where('user_id', Auth::id())
        ->count();

    return response()->json(['count' => $count]);
})->name('user.unread.count');

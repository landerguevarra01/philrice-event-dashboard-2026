<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync', [DashboardController::class, 'sync'])->name('sync');

    // Search (JSON, calls your API) and "open" (saves the selected result locally)
    Route::get('/participants/search', [ParticipantController::class, 'search'])->name('participants.search');
    Route::post('/participants/open', [ParticipantController::class, 'open'])->name('participants.open');

    // Profile + actions
    Route::get('/participants/{participant}', [ParticipantController::class, 'show'])->name('participants.show');
    Route::post('/participants/{participant}/check-in', [ParticipantController::class, 'checkIn'])->name('participants.checkin');
    Route::post('/participants/{participant}/requirements', [ParticipantController::class, 'saveRequirements'])->name('participants.requirements');
    Route::get('/participants/{participant}/certificate', [ParticipantController::class, 'certificate'])->name('participants.certificate');
});

// Public: anyone with the link can register. Everything else stays behind login.
Route::get('/registration', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/registration', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('registrations.store');

require __DIR__.'/settings.php';

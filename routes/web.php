<?php

use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', [PlaylistController::class, 'index'])->name('playlists.index');
Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'storeSong'])->name('playlists.songs.store');
Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
Route::delete('/songs/{song}', [PlaylistController::class, 'destroySong'])->name('songs.destroy');

Route::patch('/playlists/{playlist}/cover', [PlaylistController::class, 'updateCover'])
    ->name('playlists.updateCover');
    
Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
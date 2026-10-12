<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProposalController;
use App\Http\Middleware\EnsureReviewer;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('proposals.index'));

    Route::middleware(EnsureReviewer::class)->group(function () {
        Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
        Route::post('proposals/{proposal}/disapprove', [ProposalController::class, 'disapprove'])->name('proposals.disapprove');
    });

    Route::post('proposals/{proposal}/files', [ProposalController::class, 'uploadFiles'])->name('proposals.files.store');
    Route::get('proposals/{proposal}/files/{file}', [ProposalController::class, 'downloadFile'])->name('proposals.files.download');
    Route::delete('proposals/{proposal}/files/{file}', [ProposalController::class, 'destroyFile'])->name('proposals.files.destroy');

    Route::resource('proposals', ProposalController::class);
});

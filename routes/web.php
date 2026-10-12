<?php

use App\Http\Controllers\ProposalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('proposals.index'));

Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
Route::post('proposals/{proposal}/disapprove', [ProposalController::class, 'disapprove'])->name('proposals.disapprove');

Route::post('proposals/{proposal}/files', [ProposalController::class, 'uploadFiles'])->name('proposals.files.store');
Route::get('proposals/{proposal}/files/{file}', [ProposalController::class, 'downloadFile'])->name('proposals.files.download');
Route::delete('proposals/{proposal}/files/{file}', [ProposalController::class, 'destroyFile'])->name('proposals.files.destroy');

Route::resource('proposals', ProposalController::class);

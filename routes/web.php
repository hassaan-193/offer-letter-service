<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferLetterController;
use App\Http\Controllers\Candidate\OfferViewController;
use Illuminate\Support\Facades\Route;

// ─── Authentication ───────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Admin Routes (protected by AdminAuthenticate middleware) ─────────────────
Route::middleware(\App\Http\Middleware\AdminAuthenticate::class)
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Offer Letters
        Route::get('/offers', [OfferLetterController::class, 'index'])->name('offers.index');
        Route::get('/offers/create', [OfferLetterController::class, 'create'])->name('offers.create');
        Route::post('/offers', [OfferLetterController::class, 'store'])->name('offers.store');
        Route::get('/offers/{offer}', [OfferLetterController::class, 'show'])->name('offers.show');
        Route::get('/offers/{offer}/edit', [OfferLetterController::class, 'edit'])->name('offers.edit');
        Route::put('/offers/{offer}', [OfferLetterController::class, 'update'])->name('offers.update');
        Route::get('/offers/{offer}/preview', [OfferLetterController::class, 'preview'])->name('offers.preview');
        Route::post('/offers/{offer}/publish', [OfferLetterController::class, 'publish'])->name('offers.publish');
        Route::post('/offers/{offer}/revoke', [OfferLetterController::class, 'revoke'])->name('offers.revoke');
        Route::get('/offers/{offer}/download/original', [OfferLetterController::class, 'downloadOriginal'])->name('offers.download.original');
        Route::get('/offers/{offer}/download/signed', [OfferLetterController::class, 'downloadSigned'])->name('offers.download.signed');
        Route::delete('/offers/{offer}', [OfferLetterController::class, 'destroy'])->name('offers.destroy');
    });

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('admin.dashboard'));

// ─── Candidate Routes (no auth required) ─────────────────────────────────────
Route::prefix('offer')->name('candidate.offer.')->group(function () {
    Route::get('/{token}', [OfferViewController::class, 'show'])->name('show');
    Route::post('/{token}/reading-complete', [OfferViewController::class, 'readingComplete'])->name('reading-complete');
    Route::post('/{token}/sign', [OfferViewController::class, 'sign'])->name('sign');
    Route::get('/{token}/download', [OfferViewController::class, 'download'])->name('download');
});

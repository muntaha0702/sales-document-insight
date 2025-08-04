<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CsvProcessorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // CSV 
    Route::get('/csv/upload', [CsvProcessorController::class, 'index'])->name('csv.upload');
    Route::post('/csv/upload', [CsvProcessorController::class, 'upload'])->name('csv.upload.post');
    Route::get('/csv/visualize', [CsvProcessorController::class, 'visualize'])->name('csv.visualize');
    Route::get('/csv/pdf', [CsvProcessorController::class, 'generatePdf'])->name('csv.pdf');

    // SEE DATA
    Route::get('/see-data', [CsvProcessorController::class, 'showData'])->name('see.data');
    Route::get('/csv/export-pdf', [CsvProcessorController::class, 'exportPdf'])->name('csv.export.pdf');
    // Route::get('/dashboard/data',[CsvProcessorController::class,'seeData'])->name('see.data');
});

require __DIR__.'/auth.php';

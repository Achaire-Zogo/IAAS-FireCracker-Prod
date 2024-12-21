<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VirtualMachineController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    $virtualMachines = Auth::user()->virtualMachines ?? new Collection();
    return view('dashboard', compact('virtualMachines'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Routes pour les machines virtuelles
    Route::resource('virtual-machines', VirtualMachineController::class);
    Route::get('/virtual-machines/{id}/ssh', [VirtualMachineController::class, 'getSSHDetails'])->name('virtual-machines.ssh');
    Route::post('/virtual-machines/{id}/start', [VirtualMachineController::class, 'start'])->name('virtual-machines.start');
    Route::post('/virtual-machines/{id}/stop', [VirtualMachineController::class, 'stop'])->name('virtual-machines.stop');
    
    // Routes du profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

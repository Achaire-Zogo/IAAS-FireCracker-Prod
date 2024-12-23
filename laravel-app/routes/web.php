<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VirtualMachineController;
use App\Models\VmOffer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;


Route::get('/', [HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function () {
    //Route pour le Dashboard
    Route::resource('dashboard',DashboardController::class);
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

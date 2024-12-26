<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VirtualMachineController;
use App\Models\VmOffer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

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

Route::group(['middleware' => ['auth'],'namespace'=>'App\Http\Controllers','as'=>'admin.'], function () {
    // Dashboard administrateur
    Route::get('dashboard-admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Gestion des utilisateurs
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Gestion des offres VM
    Route::resource('vm-offers', \App\Http\Controllers\Admin\VmOfferController::class);

    // Gestion des images système
    Route::resource('system-images', \App\Http\Controllers\Admin\SystemImageController::class);

    // Gestion de toutes les VMs
    Route::resource('all-vms', \App\Http\Controllers\Admin\VirtualMachineController::class);

    // Gestion des clés SSH
    Route::resource('ssh-keys', \App\Http\Controllers\Admin\SSHKeyController::class);

    // Historique des VMs
    Route::get('vm-history', [\App\Http\Controllers\Admin\HistoricController::class, 'index'])->name('vm-history.index');

    // Vue d'ensemble des VMs de tous les utilisateurs
    Route::get('all-vms', [\App\Http\Controllers\Admin\VirtualMachineController::class, 'index'])->name('all-vms.index');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\NailTechController;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AnalysisController;
use App\Models\User;

Route::get('/', function () {
    $nailTech = User::where('role', 'artist')->first();
    return view('home', compact('nailTech'));
});

Route::post('/randevu-olustur', [AppointmentController::class, 'store'])->name('appointment.store');

// Tırnak Analizi Rotaları
Route::get('/tirnak-analizi', function () {
    return view('frontend.analiz');
})->name('tirnak.analiz');

Route::post('/tirnak-hesapla', [AnalysisController::class, 'analizEt'])->name('tirnak.hesapla');
// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Tırnakçı Dashboard)
Route::middleware(['auth', 'role:artist'])->prefix('panel')->group(function () {
    Route::get('/preview', [NailTechController::class, 'preview'])->name('panel.preview');
    Route::get('/appointments', [NailTechController::class, 'appointments'])->name('panel.appointments');
    Route::get('/book', [NailTechController::class, 'book'])->name('panel.book');
    Route::post('/book/pricing', [NailTechController::class, 'updatePricing'])->name('panel.book.pricing');

    Route::post('/schedule/toggle', [NailTechController::class, 'toggleScheduleBlock'])->name('panel.schedule.toggle');
    Route::post('/schedule/toggle-day', [NailTechController::class, 'toggleDayBlock'])->name('panel.schedule.toggle-day');
    Route::post('/schedule/save-day', [NailTechController::class, 'saveDayBlocks'])->name('panel.schedule.save-day');
    Route::post('/schedule/hours', [NailTechController::class, 'updateWorkHours'])->name('panel.schedule.hours');

    Route::get('/profile', [NailTechController::class, 'profile'])->name('panel.profile');
    Route::post('/profile', [NailTechController::class, 'updateProfile'])->name('panel.profile.update');

    Route::post('/appointments/{id}/status', [NailTechController::class, 'updateAppointmentStatus'])->name('panel.appointments.status');
    Route::post('/appointments/{id}/price', [NailTechController::class, 'updateAppointmentPrice'])->name('panel.appointments.price');
    Route::post('/appointments/reset', [NailTechController::class, 'resetAppointments'])->name('panel.appointments.reset');
    Route::get('/api/updates', [NailTechController::class, 'getRealtimeUpdates'])->name('panel.api.updates');

    // Notes CRUD Routes
    Route::post('/notes', [NailTechController::class, 'storeNote'])->name('panel.notes.store');
    Route::post('/notes/{id}', [NailTechController::class, 'updateNote'])->name('panel.notes.update');
    Route::delete('/notes/{id}', [NailTechController::class, 'deleteNote'])->name('panel.notes.delete');
});

use App\Http\Controllers\AdminController;

// Admin Routes (Protected)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/', [AdminController::class, 'store'])->name('admin.store');
    Route::post('/{id}', [AdminController::class, 'update'])->name('admin.update');
    Route::post('/{id}/delete', [AdminController::class, 'destroy'])->name('admin.destroy');
});

use App\Http\Controllers\FrontendController;

// Randevu Durum Takip Sayfası
Route::get('/randevu-takip/{tracking_code}', [AppointmentController::class, 'track'])->name('appointment.track');

// Dynamic Storefront/Vitrin Route (evaluated last so as not to intercept static routes)
Route::get('/{slug}', [FrontendController::class, 'show'])->name('storefront.show');


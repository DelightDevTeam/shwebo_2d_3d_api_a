<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\WelcomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

require __DIR__ . '/auth.php';

Route::get('/home', [AdminController::class, 'index'])->name('home');
Route::get('/profile', [App\Http\Controllers\HomeController::class, 'profile'])->name('profile');
Route::get('/', [App\Http\Controllers\User\WelcomeController::class, 'index'])->name('welcome');



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['auth', 'checkBanned']], function () {
  // Permissions
  Route::delete('permissions/destroy', [PermissionController::class, 'massDestroy'])->name('permissions.massDestroy');
  Route::resource('permissions', PermissionController::class);
  // Roles
  Route::delete('roles/destroy', [RolesController::class, 'massDestroy'])->name('roles.massDestroy');
  Route::resource('roles', RolesController::class);
  // Users
  Route::delete('users/destroy', [UsersController::class, 'massDestroy'])->name('users.massDestroy');
  Route::resource('users', UsersController::class);
  Route::put('users/{id}/ban', [UsersController::class, 'banUser'])->name('users.ban');
});


Route::group(['prefix' => 'user', 'as' => 'user.', 'namespace' => 'App\Http\Controllers\User', 'middleware' => ['auth', 'checkBanned']], function () {

    //profile management
    Route::put('editProfile/{profile}', [ProfileController::class, 'update'])->name('editProfile');
    Route::post('editInfo', [ProfileController::class, 'editInfo'])->name('editInfo');
    Route::post('changePassword', [ProfileController::class, 'changePassword'])->name('changePassword');
    //profile management
    Route::get('/dashboard', [App\Http\Controllers\User\WelcomeController::class, 'dashboard'])->name('dashboard');
});
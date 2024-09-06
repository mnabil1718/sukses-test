<?php

use App\Http\Controllers\AuthorController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthorController::class)->name('author.')->group(function () {
    Route::get('/authors', 'index')->name('index');
    Route::get('/authors/{id}', 'find')->name('find');
    Route::post('/authors', 'store')->name('store');
    Route::patch('/authors/{id}', 'update')->name('update');
    Route::delete('/authors/{id}', 'destroy')->name('destroy');
});

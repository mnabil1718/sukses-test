<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::controller(BookController::class)->name('book.')->group(function () {
    Route::get('/books', 'index')->name('index');
    Route::get('/books/{id}', 'find')->name('find');
    Route::post('/books', 'store')->name('store');
    Route::patch('/books/{id}', 'update')->name('update');
    Route::delete('/books/{id}', 'destroy')->name('destroy');
});

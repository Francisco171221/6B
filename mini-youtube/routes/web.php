<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('/videos', App\Http\Controllers\VideoController::class)
    ->except(['show'])
    ->middleware('auth');

Route::get('delete-video/{video_id}',[
    'as' => 'delete-video',
    'middleware' => 'auth',
    'uses'=> 'App\Http\Controllers\VideoController@delete_video'
    ]);
    

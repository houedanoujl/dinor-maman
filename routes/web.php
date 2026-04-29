<?php

use App\Livewire\ContestForm;
use App\Livewire\GalleryView;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/participer', ContestForm::class)
    ->middleware('throttle:30,1')
    ->name('contest.form');

Route::get('/galerie', GalleryView::class)
    ->middleware('throttle:120,1')
    ->name('contest.gallery');

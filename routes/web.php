<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;

Route::get('/', function () {
    return view('app');
});

Route::post('/submit-message', [AssistantController::class, 'submitMessage']);

Route::get('/delete-all-assistants', [AssistantController::class, 'deleteAllAssistants']);
Route::get('/delete-all-files', [AssistantController::class, 'deleteAllFiles']);
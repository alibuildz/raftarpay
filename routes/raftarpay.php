<?php

use Illuminate\Support\Facades\Route;
use RaftarPay\Http\CallbackController;

Route::match(['get', 'post'], '/{gateway}/callback', [CallbackController::class, 'handle'])
    ->where('gateway', 'jazzcash|easypaisa')
    ->name('raftarpay.callback');

Route::match(['get', 'post'], '/{gateway}/return', [CallbackController::class, 'handle'])
    ->where('gateway', 'jazzcash|easypaisa')
    ->name('raftarpay.return');

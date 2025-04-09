<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class Handler extends ExceptionHandler
{
    protected $levels = [
        // 
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        // Captura enlaces de firma inválida
        if ($exception instanceof InvalidSignatureException) {
            return redirect()->away('http://localhost:4200/home?email=invalid');
        }

        return parent::render($request, $exception);
    }
}

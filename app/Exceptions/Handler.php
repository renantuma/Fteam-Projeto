<?php
// app/Exceptions/Handler.php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof HttpException) {
                return response()->json([
                    'error' => $e->getMessage() ?: 'Erro na requisição'
                ], $e->getStatusCode());
            }

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }

        return parent::render($request, $e);
    }
}
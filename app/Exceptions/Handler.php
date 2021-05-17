<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof FileNotFoundException) {
            return response()->view('errors.file-not-found', compact('exception'), 404);
        } else if ($exception instanceof AuthorizationException) {
            return response()->json(['message' => __('user.permission_denied')], 403);
        } else if ($exception instanceof UnauthorizedException) {
            return response()->json(['message' => __('user.permission_denied')], 401);
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['message' => __('general.something_wrong')], 403);
        } else if ($exception instanceof ModelNotFoundException) {
            return response()->json(['message' => __('general.data_not_found')], 403);
        } if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors()
            ], 422);
        }


        return parent::render($request, $exception);
    }
}

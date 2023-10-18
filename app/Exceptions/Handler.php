<?php

namespace App\Exceptions;

use App\Constants\DatabaseConnectionConstant;
use App\WebServices\AWS\SecretsManagerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->isConnectionError($e)) {
                cache()->forget('aws_secret_password');
                SecretsManagerService::getPasswordCache();
            }
        });
    }

    public function render($request, Throwable $e)
    {
        if (
            ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException)
            && $request->wantsJson()
        ) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } elseif ($this->isConnectionError($e) && $request->wantsJson()) {
            return response()->json([
                'message' => __(''),
            ], Response::HTTP_CONFLICT);
        }

        return parent::render($request, $e);
    }

    private function isConnectionError(Throwable $e)
    {
        return $e instanceof QueryException
            && Arr::first($e->errorInfo) == 'HY000'
            && Str::contains($e->getMessage(), DatabaseConnectionConstant::exceptionRetryable());
    }
}

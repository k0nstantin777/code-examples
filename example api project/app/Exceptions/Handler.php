<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
		$this->renderable(function (NotFoundHttpException|ModelNotFoundException $e, $request) {
			if ($request->is('api/*')) {
                Log::channel('api')->warning($e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all()
                ]);
				return response()->apiError('Resource not found');
			}
		});

		$this->renderable(function (AccessDeniedHttpException|AuthorizationException|AuthenticationException $e, $request) {
			if ($request->is('api/*')) {
                Log::channel('api')->info($e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all()
                ]);
				return response()->apiError('Access Denied');
			}
		});

		$this->renderable(function (ValidationException $e, $request) {
			if ($request->is('api/*')) {
                Log::channel('api')->debug($e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all()
                ]);
				return response()->apiError($e->errors());
			}
		});

		$this->renderable(function (\Throwable $e, $request) {
			if ($request->is('api/*')) {
                Log::channel('api')->error($e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all()
                ]);
				return response()->apiError('Server error');
			}
		});
    }
}

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function convertValidationExceptionToResponse(ValidationException $e, $request): Application|Response|JsonResponse|Redirector|RedirectResponse|\Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Foundation\Application
    {
        if ($e instanceof ValidationException && $request->expectsJson() && !$request->is('admin/*')) {
                return response()->json([
                    'message' => ["error" => $e->getMessage()],
                    "data" => $e->errors(),
                ], 422, options: JSON_UNESCAPED_UNICODE);
        } else {
            return $this->shouldReturnJson($request, $e)
                ? $this->invalidJson($request, $e)
                : $this->invalid($request, $e);
        }
    }
    protected function invalid($request, ValidationException $exception): Application|Response|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        /**
         * TODO:
         * отрефакторить этот кусок
         */
        if ($exception instanceof ValidationException && request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'messageError' => ["error" => $exception->getMessage()],
                'code' => $exception->status,
                'data' => $exception->errors(),
            ], 422, options: JSON_UNESCAPED_UNICODE);
        } else {
            return redirect($exception->redirectTo ?? url()->previous())
                ->withInput(Arr::except($request->input(), $this->dontFlash))
                ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
        }
    }


    public function convertExceptionToArray(Throwable $e): array
    {
        if (config('app.debug')) {
            if ($e instanceof AccessDeniedHttpException) {
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                    'code' => $e->getStatusCode(),
                    'data' => null,
                ];
            }
            return [
                'status' => false,
                'messages' => [$e->getMessage()],
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ];
        } else {
            return [
                'status' => false,
                'messageError' => ["error" => $e->getMessage()],
                'data' => [],
            ];
        }
    }
}

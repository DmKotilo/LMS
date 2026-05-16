<?php

namespace Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    // обработка ответа для клиента
    public function sendResponse($message, $result = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return response()->json($response, 200, $headers, options: JSON_UNESCAPED_UNICODE);
    }

    // обработка ошибок для клиента
    public function sendError($message = null, $data = null, $code = 404, $errors = null, \Throwable|\Exception $exception = null): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message,
            'data' => $data,
        ];

        if ($message && $exception) {
            $response['errors'] = [
                $exception->getMessage()
            ];
            $code = $exception->getCode();
        } elseif ($exception) {
            $response['success'] = $exception->getMessage();
            $code = $exception->getCode();
        }

        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return response()->json($response, $code, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function authCheck(){
        if (!auth()->check()) {
            return redirect()->to(
                url()->previous() . (str_contains(url()->previous(), '?') ? '&' : '?') . 'modal=login'
            );
        }else{
            return redirect()->back()->with([
                'success' => 'Пользователь авторизован'
            ]);
        }
    }
}

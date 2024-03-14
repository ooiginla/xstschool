<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InternalAppException extends Exception
{

    protected $error_codes = ErrorCode::CODES;

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    { 
        // ...
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // ...
    }
 

    public function render(Request $request): JsonResponse
    {
        if ($request->is('api/*') || $request->ajax()) 
        {
            $error_key = $this->getMessage();

            return response()->json([
                'status' => false,
                'code' => $this->getInternalCode($error_key),
                'message' => $this->getInternalMessage($error_key),
                'data' => []
            ], $this->getInternalHttpCode($error_key));
        }

        return false;
    }

    public function getInternalCode($error_key){
        return $this->error_codes[$error_key]['message'];
    }

    public function getInternalMessage($error_key){
        return $this->error_codes[$error_key]['code'];
    }

    public function getInternalHttpCode($error_key){
        return $this->error_codes[$error_key]['http_code'];
    }

}

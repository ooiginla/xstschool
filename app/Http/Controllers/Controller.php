<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendFinalResponse($dto)
    {
        $data = [];
        $data['status'] = $dto->req_status;
        $data['code'] = $dto->code;
        $data['message'] = $dto->message;
        $data['data'] = $dto->data;

        return response()->json($data, $dto->http_code);
    }
}

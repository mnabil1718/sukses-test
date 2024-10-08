<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HttpException extends Exception
{

    /**
     * Report the exception
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception to an HTTP Response
     *
     * @param Request $request
     * @return void
     */
    public function render(Request $request)
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
        ], $this->code);
    }
}

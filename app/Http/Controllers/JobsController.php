<?php

namespace App\Http\Controllers;

use App\DataHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobsController extends Controller
{
    public function queueMail(Request $request): Response
    {
        $dataHandler = new DataHandler();
        $response = $dataHandler->queueMail($request);

        return $response;
    }

    public function queueSMS(Request $request): Response
    {
        $dataHandler = new DataHandler();
        $response = $dataHandler->queueSMS($request);

        return $response;
    }
}

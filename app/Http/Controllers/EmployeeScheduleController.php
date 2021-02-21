<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class EmployeeScheduleController extends Controller
{
    public function getWorkSchedule(Request $request): JsonResponse
    {
        var_dump($request->all()); die();
        return new JsonResponse(['Endpoint not yet implemented.'], JsonResponse::HTTP_NOT_IMPLEMENTED);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AttendanceApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __invoke(Request $request, AttendanceApi $api): JsonResponse
    {
        return response()->json($api->get($request->boolean('fresh')));
    }
}

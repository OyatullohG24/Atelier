<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalculateService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(protected CalculateService $calculate) {}


    public function sell(Request $request)
    {
        $response = $this->calculate->index($request->clothes_id, $request->count);
        return $response;
    }
}

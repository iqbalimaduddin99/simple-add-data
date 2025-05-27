<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class NumbersController extends Controller
{
    public function fibnacci($n = 5)
    {
        $fibos = [];
        for ($i = 0; $i <= $n; $i++) {
            if ($i == 0) {
                $fibos[$i] = 0;
            } elseif ($i == 1) {
                $fibos[$i] = 1;
            } else {
                $fibos[$i] = $fibos[$i - 1] + $fibos[$i - 2];
            }
        }
        return ['result' => $fibos[$n]];
    }

    public function fibonacciSum(Request $request)
    {
        $n1 = $request->input('n1');
        $n2 = $request->input('n2');

        $fib1 = $this->fibnacci($n1)['result'];
        $fib2 = $this->fibnacci($n2)['result'];
        $sum = $fib1 + $fib2;

        return ApiResponse::success([
            'fib1' => $fib1,
            'fib2' => $fib2,
            'sum' => $sum,
        ]);
    }
}

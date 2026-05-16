<?php

namespace Connect\Http\Controllers;

use App\Http\Controllers\Controller;
use Connect\Models\CheckConnection;
use Illuminate\Http\Request;

class CheckConnectController extends Controller
{
    public function check(Request $request)
    {
        $data = $request->validate(['date_current' => 'required']);
        $checkCreate = CheckConnection::create($data);

        return response()->json($checkCreate);
    }
}

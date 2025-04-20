<?php


namespace App\Http\Controllers\Connect;


use App\Http\Controllers\Controller;
use App\Models\Connection\CheckConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckConnectController extends Controller
{
    public function check(Request $request){
        $data = $request->validate(["date_current" => "required"]);
        $check_create = CheckConnection::create($data);
        return response()->json($check_create);
    }
}

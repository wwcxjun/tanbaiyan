<?php

/*
|--------------------------------------------------------------------------
| 坦白言——小程序后端
| www.tanbaiyan.com
| 觉得不错的话 记得在Github上点个star哦~
|
*/

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Tanbai;
use Validator;

class LookController extends Controller
{
    /**********************
     *  此函数用于查看坦白
     *  需要的参数：token
     **********************/
    public function index(Request $request)
    {
        //验证提交的数据
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 300, 'error' => 'Parameter error'], 300);
        }

        $user_res = User::where('token', $request->token)->first();
        if($user_res){
            //判断一下token是否到期
            $now = date('Y-m-d H:i:s');
            $token_time = date('Y-m-d H:i:s', $user_res->token_time);
            $overtime = date('Y-m-d H:i:s', strtotime("{$token_time} + 5 hours"));
            if($now > $overtime){
                return response()->json(['status' => 300, 'error' => 'Token Timeout'], 300);
            }else{
                //获取坦白
                $tanbai_res = Tanbai::where('receive_user_id', $user_res->id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(15);
                return response()->json(['status' => 200, 'data' => $tanbai_res], 200);
            }
        }else{
            return response()->json(['status' => 400, 'error' => 'The user does not exist'], 400);
        }
    }
}

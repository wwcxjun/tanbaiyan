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
use App\Tanbai;
use App\User;
use Validator;

class SendController extends Controller
{
    /************************************
     *  小程序端需要提交的字段
     *  content(内容)
     *  user_id(接收用户的ID)
     *  signature(署名)
     *  token(提交数据用户的token)
     ***********************************/
    public function store(Request $request)
    {
        //验证提交的数据
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|max:10',
            'content' => 'required|min:2|max:200',
            'signature' => 'required|min:2|max:32',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 300, 'error' => 'Parameter error'], 300);
        }

        $token = $request->token;
        $user_res = User::where('token', $token)->first();
        //提交数据的用户
        if($user_res){
            //判断一下token是否到期
            $now = date('Y-m-d H:i:s');
            $token_time = date('Y-m-d H:i:s', $user_res->token_time);
            $overtime = date('Y-m-d H:i:s', strtotime("{$token_time} + 5 hours"));
            if($now > $overtime){
                return response()->json(['status' => 300, 'error' => 'Token Timeout'], 300);
            }else{
                //需要先判断一下是否重复提交
                $repeat_res = Tanbai::where('receive_user_id', $request->user_id)
                    ->where('send_user_id', $user_res->id)
                    ->where('content', $request->content)
                    ->where('signature', $request->signature)
                    ->first();
                if($repeat_res){
                    return response()->json(['status' => 200], 200);
                }
                //获取提交数据的用户ID
                $add_tanbai = new Tanbai;
                $add_tanbai->receive_user_id = $request->user_id;
                $add_tanbai->send_user_id = $user_res->id;
                $add_tanbai->content = $request->content;
                $add_tanbai->signature = $request->signature;
                $res = $add_tanbai->save();
                if($res){
                    return response()->json(['status' => 200], 200);
                }else{
                    return response()->json(['status' => 500, 'error' => 'Unknown error occurred'], 500);
                }
            }
        }else{
            return response()->json(['status' => 400, 'error' => 'The user does not exist'], 400);
        }
    }
}

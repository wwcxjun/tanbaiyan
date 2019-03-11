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
use Validator;

class LoginController extends Controller
{
    /**********************************************************
     *  通过提交过来的CODE调用自定义函数get_openid获取openid
     *  判断数据库是否存在openid 如果存在 刷新TOKEN及TOKEN时间
     *  如果不存在则创建之
     **********************************************************/
    public function login(Request $request)
    {
        //数据验证
        //验证提交的数据
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 300, 'error' => 'Parameter error'], 300);
        }

        //通过提交过来的CODE 发起请求获取OPENID
        $res = $this->get_openid($request->code);
        $res_obj = json_decode($res);
        if(isset($res_obj->errcode)){
            return response()->json(['status' => 300, 'error' => 'Code invalid']);
        }
        //判断openid
        $user_res = User::where('openid', $res_obj->openid)->first();
        $token = encrypt(md5($request->code.md5(uniqid(md5(microtime(true)),true))));
        if($user_res){
            //此时存在该用户 刷新TOKEN及时间
            $res = User::where('openid', $res_obj->openid)
                ->update([
                    'token' => $token,
                    'token_time' => time()
                ]);
            if($res){
                return response()->json(['status' => 200, 'token' => $token, 'uid' => $user_res->id] ,200);
            }else{
                return response()->json(['status' => 400, 'error' => 'Unknown error'], 400);
            }
        }else{
            /*return response()->json(['error' => 'Resource not found.'], 400);*/
            //此时不存在该用户 需要新增用户
            $user = new User;
            $user->openid = $res_obj->openid;
            $user->token = $token;
            $user->token_time = time();
            $res = $user->save();
            if($res){
                return response()->json(['status' => 200, 'token' => $token, 'uid' => $user->id], 200);
            }
        }
    }

    /**********************************************************
     *  通过传递过来的CODE 调用官方API返回该用户的OPENID等信息
     *  返回JSON数据格式
     **********************************************************/
    public function get_openid($code)
    {
        $appid = getenv('MINI_APPID');
        $secret = getenv('MINI_APPSECRET');
        $code = $code;
        $api_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        return $data = file_get_contents($api_url);
    }
}

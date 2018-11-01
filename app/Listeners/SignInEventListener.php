<?php

namespace App\Listeners;

use App\Events\SignInEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Model\User;
use \Firebase\JWT\JWT;

class SignInEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SignInEvent  $event
     * @return void
     */
    public function handle(SignInEvent $event)
    {
        $data = $event->data;
        return $this->createJwt($data);
    }


    /**
     * 身份验证创建 JWT 令牌,并存令牌
     * @param  array $params 登录参数
     * @return string
     */
    private function createJwt($params) {

        $data = [
            //用户名
            "iss" => $params['user_id'],
            //过期时间,15天
            "exp" => time()+ env('JWT_EXP', 3600*24*15),
            ////token 创建时间
            "iat" => time(),
            ////当前token唯一标识
            //str_ireplace,即最后一个参数中的所有"."用空白代替,即去掉"."号
            //microtime — 返回当前 Unix 时间戳和微秒数,如1514962593.1521
            "jti" => str_ireplace('.', '', microtime(true)),
            'data' => $params
        ];

        //jwt对数据加密
        $jwt = JWT::encode($data, env('JWT_KEY', ''), 'HS256');

        $setJwtParams = [
            //key为jwt加密后的数据
            'key' => $jwt,
            'expire' => env('JWT_EXP', 3600*24*15),
            'values' => [
                'uid' => $params['user_id'],
                'iat' => $data['iat'],//jwt创建时间
            ],
        ];
        $this->setJwt($setJwtParams);//把jwt存入redis
        
        return [
            'meta'=>[
                'code'=>'200',
                'message'=>'login success'
            ],
            'data'=>[
                'token'=>$jwt,
            ],
        ];
        
    }
        
    /**
     * 把jwt写入redis hash类型
     * @param [type] $params [description]
     */
    private function setJwt($params)
    {
        return User::setJwt($params['key'], $params['expire'], $params['values']);
    }

    
}

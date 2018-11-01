<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\User;
use \Firebase\JWT\JWT;
use Cookie;

class JwtLogout
{
    public $encodeToken;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        if(!$this->checkToken()) {
            Cookie::queue(env('ZLINK_TK', 'ZLINK_TK'), null, -1);
            Cookie::queue(env('ZLINK_USERINFO', 'ZLINK_USERINFO'), null, -1);
            //判断是不是ajax
            if ($request->ajax())
            {
                return
                    [
                        'meta'=>[
                            'code'=>401,
                            'message'=>'Unauthorized',
                        ]
                    ];
            }
            //返回到登录页面,后面的含义?????
            return redirect(changeUrl(route('login', ['alert' => $request->input('alert', 0)])));
        }
        $request->jwt = [
            'encode'=>$this->encodeToken,
            'decode'=>$this->checkToken(),
        ];

        return $next($request);
    }



    /**
     * 验证token 是否过期，是否正常
     *
     * @return false|object
     */
    public function checkToken() {

        if(! $decodeToken = $this->parseToken($header = 'authorization', $method = 'bearer', $query = 'ZLINK_TK')) {
            return false;
        }
        return $decodeToken;

    }

    /**
     * 两种方式解析token,一个是http头，一个是传参数,返回解析后的token
     *
     * @param string $header
     * @param string $method
     * @param string $query
     *
     * @return false|object
     */
    public function parseToken($header = 'authorization', $method = 'bearer', $query = 'ZLINK_TK')
    {

        if (! $token = $this->parseAuthHeader($header, $method)) {
            if (! $token = \Request::input($query)) {
                if(! $token = \Request::cookie($query)) {
                    return false;
                }
            }
        }
        //count — 计算数组中的单元数目，或对象中的属性个数
        //explode — 使用一个字符串分割另一个字符串
        if (count(explode('.', $token)) !== 3) {
            return false;

        }
        if(User::isJwtExists($token) != 1) {
            return false;
        }
        $this->encodeToken = $token;
        $decodeToken = (array)JWT::decode($token, env('JWT_KEY', ''), array('HS256'));

        return $decodeToken;
    }

    /**
     * Parse token from the authorization header.
     *
     * @param string $header
     * @param string $method
     *
     * @return false|string
     */
    protected function parseAuthHeader($header = 'authorization', $method = 'bearer')
    {
        $header = \Request::header($header);

        if (! starts_with(strtolower($header), $method)) {
            return false;
        }

        //trim — 去除字符串首尾处的空白字符（或者其他字符）
        return trim(str_ireplace($method, '', $header));
    }

}

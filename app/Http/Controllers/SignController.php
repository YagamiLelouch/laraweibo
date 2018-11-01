<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Model\User;
use Redis;
use Jenssegers\Date\Date;
use \Firebase\JWT\JWT;
use App\Events\SignInEvent;
use App\Events\LogEvent;
use App\Jobs\SendEmail;
use Cookie;
use Agent;

class SignController extends Controller
{

    //中间件
    public function __construct()
    {
        $this->middleware('jwt', ['only' => ['isLogin','logout']]);
//        $this->middleware('logout' , ['only' => ['getOut']]);
    }

    /**
     * 注册的view,访问需要进行的操作
     */
    public function signUpView(Request $request)
    {
        //判断是否已经登录
        //从cookie中获取token
        $params = [
            //获取token
            'token' => Cookie::get(env('ZLINK_TK', 'ZLINK_TK')),
            //time — 返回当前的 Unix 时间戳
            'timestamp' => time()
        ];
        $login = $this->checkToken($params);
        if ($login) {
            echo "<script>parent.location.href='".changeUrl(route('home'))."';</script>";
            exit;
        }

        return view('sign.up');
    }

    /**
     * 发送注册短信或重置短信
     */
    public function sendCode(Request $request)
    {

        //设置会接收的请求,并定义成数组,然后对数组进行格式化操作
        $params = $request->only('phone', 'action');
        $params['phone'] = strtolower(trim($params['phone']));
        $params['action'] = strtolower(trim($params['action']));

        //对接收到的参数进行验证
        $validator = Validator::make($params, [
            'phone' => 'required|phone:CN',
            'action' => 'required|in:reset_password,sign_up',
        ]);
        //判断验证是否通过
        if ($validator->fails()) {
           return [
               'meta'=>[
                   'code'=>'400',
                   'message'=>$validator->errors()->all(),
               ]
           ];
        }

        //通过以后
        //获取用户浏览器和ip信息
        $params['agent'] = Agent::getUserAgent();
        $params['ip']=getClientIP();

        //判断是注册还是重置
        if ($params['action'] == 'sign_up') {
            $content = '注册ZLink账号';
        } elseif ($params['action'] == 'reset_password') {
            $content = '重置ZLink密码';
        }


        //发送注册验证码部分
        //生成随机密码
        $params['code'] = rand(100000, 999999);
        $params['add_time'] = time();
        //2小时后过期
        $params['expire'] = time()+ intval(env('PHONE_CODE_EXPIRE', 60*60*2));
        //key为信息的存储位置
        $key = 'apollo:code:phone:'.$params['phone'];
        Redis::connection('write')->hMset($key, $params);
        Redis::connection('write')->expire($key, $params['expire']);

        //发送短信
        $data = $this->sendSms($params['phone'], '您的短信验证码是'.$params['code'].'。本条短信用于'.$content.'，请勿泄露。'.intval(env('PHONE_CODE_EXPIRE', 60*60*2)/3600).'小时之内有效。');

        //判断短信发送状况
        if ($data['code'] == 0) {
            return $this->returnCode(200);
        } else {
            return $this->returnCode(500, '', $data);
        }

    }

    /**
     * 点击注册按钮的注册动作,填写注册信息,提交是的一些信息的判断,写入
     * @param  Request $request [description]
     * @return [type] [description]
     */
    public function signUp(Request $request)
    {
        //定义接收的参数形成数组
        $params = $request->only('method', 'username', 'phone', 'email', 'password', 'check', 'code');

        //格式化用户名\手机号\邮箱,都变统一的小写,都进行取出首位空白
        $params['username'] = strtolower(trim($params['username']));
        $params['phone'] = strtolower(trim($params['phone']));
        $params['email'] = strtolower(trim($params['email']));

        //验证
        $validator = Validator::make($params, [
            'method' => 'required|in:email,phone',
            'username' => ['required', 'min:3', 'max:20', 'regex:/^(?!(?:[0-9]*$))[a-z0-9]{2,20}$/'],
            'password' => 'required|min:6|max:32',
            'phone' => 'required_if:method,phone|phone:CN',
            'email' => 'required_if:method,email|email|max:90',
//            'check' => 'required|accepted',
        ]);

        //判断验证是否通过
        if ($validator->fails()) {
            return [
                'meta'=>[
                    'code'=>'400',
                    'message'=>$validator->errors()->all(),
                ]
            ];
        }


        //字段验证通过以后
        $params['agent'] = Agent::getUserAgent();
        $params['ip'] = getClientIp();
        $params['password'] = md5($params['password']);
        $params['ip'] = $request->input('ip','172.18.5.96');
        $params['agent'] = $request->input('agent','Agent');
        $params['action'] = 'sign_up';

        //判断是哪一种注册方式
        switch ($params['method']) {
            case 'email':
                //发送邮件
                return $this->emailConfirmSend($params);
                break;
            case 'phone':
                //验证手机验证码
                $data=$this->upPhone($params);
                //手机注册自动登录成功，写入Cookie
                if(isset($data['data']['token'])){
                    Cookie::queue('ZLINK_TK', $data['data']['token'], env('JWT_EXP', 21600));
                    Cookie::queue('ZLINK_NAME', $params['phone'], env('NAME_EXP', 54000));
                    Cookie::queue('ZLINK_USERINFO', json_encode($data['user']), env('USERINFO_EXP', 21600));
                }
                return response()->json($data);
                break;
            default:
                return $this->returnCode(400, '仅支持邮箱或手机注册');
                break;
        }


    }

    /**
     * 发送邮件,点击注册的时候会触发
     * @param  [type]   $params [description]
     * @return [type]           [description]
     */
    public function emailConfirmSend($params)
    {
        //每天只能发10封
        $key_num = 'apollo:num:email:'.$params['email'];
        //查看哈希表 key 中，给定域 field 是否存在
        if (Redis::connection('read')->hExists($key_num, $params['action'])) {
            //为哈希表 key 中的域 field 的值加上增量 increment
            Redis::connection('write')->hincrby($key_num, $params['action'], 1);
        } else {
            Redis::connection('write')->hincrby($key_num, $params['action'], 1);
            Redis::connection('write')->expire($key_num, Date::today()->add(env('MAIL_CODE_NUM_DAY', 1).' day')->timestamp - time());
        }
        if (intval(Redis::connection('read')->hGet($key_num, $params['action'])) > intval(env('MAIL_CODE_NUM', 10))) {
            return $this->returnCode(401, '今天发送邮件数量超过上限');
        }

        //uniqid — 生成一个唯一ID
        $params['code'] = strtolower(md5(uniqid()));
        $params['add_time'] = time();
        $params['expire'] = time()+ intval(env('MAIL_CODE_EXPIRE', 60*60*24*7));//7天后过期
        //判断是注册的还是重置的
        if ($params['action'] == 'sign_up') {
            $key = 'apollo:code:email:'.$params['email'];
        } else {
            $key = 'apollo:code:reset:'.$params['code'];
        }
        //同时将多个 field-value (域-值)对设置到哈希表 key 中,用来发邮件验证使用
        Redis::connection('write')->hMset($key, $params);
        Redis::connection('write')->expire($key, $params['expire']);

        //判断动作是注册还是重置,并设置邮件的标题\视图到数组
        switch ($params['action']) {
            case 'sign_up':
                $params['subject'] = "邮箱注册验证";
                $params['view'] = "sign_up";
                break;
            case 'reset_password':
                $params['subject'] = "重置密码";
                $params['view'] = "reset_password";
                break;
            default:
                return $this->returnCode(500);
                break;
        }
        $data = $params;

        //Jobs目录用于存放队列任务，应用中的任务可以被队列化，也可以在当前请求生命周期内同步执行
        //onQueue指定任务所属的队列
        $job = (new SendEmail($params))->onQueue('apollo:emails');
        //dispatch将任务推送到队列上
        $this->dispatch($job);
        return $this->returnCode(200);
    }

    /**
     * 邮件注册,在邮箱中收到注册邮件后,点击链接进行的注册
     * @param  Request $request [description]
     * @return [type] [description]
     */
    public function emailUp(Request $request)
    {
        $params = $request->only('email', 'code');

        $validator = Validator::make($params, [
            'email' => 'required|email|max:90',
            'code' => 'required|max:32',
        ]);
        if ($validator->fails()) {
            $info = [
                'title' => '邮箱注册失败，请重试！',
                'content' => $validator->errors()->all(),
                'href' => route('home'),
                'a' => '点击返回主页。',
            ];
//            return view('common._info', compact('info'));
        }

        $params['method'] = 'email';
        $params['agent'] = Agent::getUserAgent();
        $params['ip'] = getClientIp();

        //注册的code确认,$user最后返回的是用户的注册信息的数组形式
        $user = $this->emailConfirm($params);
        if (!isset($user['user_id'])) {
            return $user;
        }
        $key = 'apollo:code:email:'.$params['email'];
        //信息用完就全部删除了
        Redis::connection('write')->del($key);

//        //写注册日志,创建注册的数组信息
//        $info = [
//            'theme' => 'user',
//            'title' => 'sign_up',
//            'info' => [
//                'uid'=>intval($user['usr_id']),
//                'controller'=>'sign',
//                'action'=>'postUp',
//                'desc' => '注册成功',
//                'add_time' => time(),
//                'ip' => $request->input('ip','127.0.0.1'),
//                'agent' => $request->input('agent', 'Agent'),
//            ]
//        ];
//        //event 函数配送指定 事件 到所属的侦听器
//        event(new LogEvent($info));

        //成功后自动登录
        //SignInEvent事件做以下事情，生成jwt,把jwt写入redis
        $data = event(new SignInEvent($user))[0];

//        //写登录日志,创建登录的数组信息
//        $info = [
//            'theme' => 'user',
//            'title' => 'sign_in',
//            'info' => [
//                'uid'=>intval($user['usr_id']),
//                'controller'=>'sign',
//                'action'=>'postIn',
//                'desc' => '登录成功',//日志描述
//                'add_time' => time(),
//                'ip' => $request->input('ip','127.0.0.1'),
//                'agent' => $request->input('agent', 'Agent'),
//            ]
//        ];
//        //将LogEvent($info)配送到监听器
//        event(new LogEvent($info));

        $data['data']['user'] = $user;


        //自动登录成功，写入Cookie
        //为什么不执行这个????????


        if ($data['meta']['code'] != 200) {
            $info = [
                'title' => '邮箱注册失败，请重试！',
                'href' => changeUrl(route('home')),
                'a' => '点击返回主页。',
            ];
            //$data['data'][0]为自己设置的错误信息的值
            if (isset($data['data'][0])) {
                $info['content'] = [$data['data'][0]];
            } else {
                //[$data['meta']['message']]可能是系统默认的错误信息
                $info['content'] = [$data['meta']['message']];
            }
            return view('common._info', compact('info'));
        } else {
            $info = [
                'title' => '邮箱注册成功！',
                'content' => [],
                'href' => changeUrl(route('home')),
                'a' => '点击返回主页。',
            ];
            //没有写自己登录就可以登录,难道用cookie是登录的原理??????????
            if(isset($data['data']['token'])){
                Cookie::queue('ZLINK_TK', $data['data']['token'], env('JWT_EXP', 21600));
                Cookie::queue('ZLINK_NAME', $params['email'], env('NAME_EXP', 3600*24*10));
                Cookie::queue('ZLINK_USERINFO', json_encode($data['data']['user']), env('USERINFO_EXP', 3600*24*10));
            }
            return redirect()->route('home');
        }
    }

    /**
     * 验证邮件的code
     * @param  Request $request [description]
     * @return string           [description]
     */
    public function emailConfirm($params)
    {
        $validator = \Validator::make($params, [
            'code' => 'required|size:32',
            'email' => 'required|email|max:90',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }

        $key = 'apollo:code:email:'.$params['email'];
        //定义$r为用户存在redis的注册信息
        $r = Redis::connection('read')->hGetAll($key);
        //验证code是否存在\是否相等
        if (!isset($r['code']) || $r['code'] != $params['code']) {
            return $this->returnCode(400, '', ['签名错误']);
        }
        //用户名唯一性验证???表达式的意思?
        $validator = \Validator::make(['usrename' => $r['username']], [
            'username' => 'unique:user_info,username',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }

        //注册成功后，返回注册成功的用户信息
        //toArray 方法将集合转换成 PHP 数组
        return User::signUp($r)->toArray();
    }

    /**
     * 手机确认注册,验证码确认,点击注册的时候会确认
     * @param  Request $request [description]
     * @return [type]           C[description]
     */
    public function upPhone($params)
    {
        $validator = \Validator::make($params, [
            'code' => 'required|size:6',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400, '', $validator->errors()->all());
        }

        $key = 'apollo:code:phone:'.$params['phone'];
        $r = Redis::connection('read')->hGetAll($key);
        //判断code是否一致
        if (!isset($r['code']) || $r['code'] != $params['code']) {
            return $this->returnCode(400, '', ['验证码不正确']);
        }

//        Redis::connection('write')->del($key);

        $user = User::signUp($params)->toArray();
        if (!isset($user['user_id'])) {
            return $user;
        }

        //写注册日志
        $info = [
            'theme' => 'user',
            'title' => 'sign_up',
            'info' => [
                'uid'=>intval($user['user_id']),
                'controller'=>'sign',
                'action'=>'postUp',
                'desc' => '注册成功',
                'add_time' => time(),
                'ip' => $params['ip'],
                'agent' => $params['agent'],
            ]
        ];
        event(new LogEvent($info));

        //成功后自动登录
        //SignInEvent事件做以下事情，生成jwt,把jwt写入redis
        $data = event(new SignInEvent($user))[0];

        //写登录日志
        $info = [
            'theme' => 'user',
            'title' => 'sign_in',
            'info' => [
                'uid'=>intval($user['user_id']),
                'controller'=>'sign',
                'action'=>'postIn',
                'desc' => '登录成功',//日志描述
                'add_time' => time(),
                'ip' => $params['ip'],
                'agent' => $params['agent'],
            ]
        ];
        event(new LogEvent($info));

        $data['user'] = $user;

        return $data;
    }

    /**
     * 手机重置密码认证,验证码确认
     * @param  [type]   $params [description]
     * @return [type]           [description]
     */
    public function sendResetPhone($params)
    {
        $key = 'apollo:code:phone:'.$params['phone'];
        $r = Redis::connection('read')->hGetAll($key);
        //核对code
        if (!isset($r['code']) || $r['code'] != $params['code']) {
            return $this->returnCode(400, '', ['验证码不正确']);
        }

        //验证成功后删除key
//        Redis::connection('write')->del($key);

        //重新写入新的缓存信息
        //uniqid — 生成一个唯一ID
        $params['code'] = strtolower(md5(uniqid()));
        $params['add_time'] = time();
        $params['expire'] = time()+ intval(env('MAIL_CODE_EXPIRE', 60*60*24*7));//7天后过期
        $key = 'apollo:code:reset:'.$params['code'];
        Redis::connection('write')->hMset($key, $params);
        Redis::connection('write')->expire($key, $params['expire']);

        return $this->returnCode(200, '', ['code' => $params['code']]);
    }

    /**
     * 登录界面的进入
     */
    public function loginView(Request $request)
    {
        //判断是否已经登录
        //从cookie中获取token
        $params = [
            //获取token
            'token' => Cookie::get(env('ZLINK_TK', 'ZLINK_TK')),
            //time — 返回当前的 Unix 时间戳
            'timestamp' => time()
        ];
        $login = $this->checkToken($params);
        if ($login) {
            echo "<script>parent.location.href='".changeUrl(route('home'))."';</script>";
            exit;
        }
        return view('sign.in');
    }

    /**
     * 检查token是否存在
     */
    public function checkToken($params)
    {
       $token = $params['token'];
        //通过"."分割token
        if (@count(explode('.', $token)) !== 3) {
//            \Log::error(__METHOD__.'=>Wrong number of segments');
            return false;

        }
        //读取redis里面的jwt是否有token
        if(User::isJwtExists($token) != 1) {
//            \Log::error(__METHOD__.'=>token not exists or expire');
            return false;
        }
        $this->encodeToken = $token;

        //解码token
        $decodeToken = (array)JWT::decode($token, env('JWT_KEY', ''), array('HS256'));

        if ($decodeToken) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * 点击登录
     */
    public function login(Request $request)
    {
        //获取请求的特定参数形成数组
        $params = $request->only('account', 'password');
        //格式化一些参数
        $params['account'] = strtolower(trim($params['account']));
        $rules['password'] = 'required';

        //validator验证
        //filter_var — 使用特定的过滤器过滤一个变量,本方法用来过滤邮件,成功则返回过滤的数据
        if(filter_var($params['account'], FILTER_VALIDATE_EMAIL)) {
            $rules['account'] = 'required|email|max:90';
            $params['method'] = 'email';
            //匹配手机号
        } elseif(preg_match("/1[3458]{1}\d{9}$/",$params['account'])){
            $rules['account'] = 'required|phone:CN';
            $params['method'] = 'phone';
            //其他为名字
        } else {
            $rules['account'] = ['required', 'min:3', 'max:20', 'regex:/^(?!(?:[0-9]*$))[a-z0-9]{2,20}$/'];
            $params['method'] = 'name';
        }
        //获取登录方式并验证
        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            return [
                'meta'=>[
                    'code'=>'400',
                    'message'=>$validator->errors()->all(),
                ]
            ];
        }

        //判断错误次数
        //intval — 获取变量的整数值
        //hGet返回哈希表 key 中给定域 field 的值。
        //获取登陆次数
        if (intval(Redis::connection('read')->hGet('hades:num:account:'.$params['account'], 'login')) > intval(env('LOGIN_NUM', 999999))) {
            return [
                'meta'=>[
                    'code'=>'401',
                    'message'=>'失败次数过多，今日已锁定',
                ]
            ];
        }
        //通过后获取一些客户端信息
        //Agent为一个用户设备信息的获取器
        $params['agent'] = Agent::getUserAgent();
        //获取用户ip
        $params['ip'] = getClientIp();

        //将密码md5加密
        $params['password'] = md5($params['password']);

        //登录操作
        $user=User::login($params);

        //查不到数据或id不存在
        if(null == $user || !($user['user_id'])) {
            return $this->returnCode(401);
        }
        //toArray()将集合转换成纯 PHP 数组
        $user = $user->toArray();

        //生成jwt,把jwt写入redis
        //event 函数配送指定 事件 到所属的侦听器：
        $data = event(new SignInEvent($user))[0];
        //写登录日志
        $info = [
            'theme' => 'user',
            'title' => 'sign_in',
            'info' => [
                'uid'=>intval($user['user_id']),
                'controller'=>'sign',
                'action'=>'postIn',
                'desc' => '登录成功',//日志描述
                'add_time' => time(),
                'ip' => $request->input('ip','127.0.0.1'),
                'agent' => $request->input('agent', 'Agent'),
            ]
        ];
        event(new LogEvent($info));

        $data['data']['user'] = $user;

        //登录成功，写入Cookie
        if (isset($data['data']['token'])) {
            //cookie的创建
            Cookie::queue('ZLINK_TK', $data['data']['token'], env('JWT_EXP', 21600));
            Cookie::queue('ZLINK_NAME', $params['account'], env('NAME_EXP', 54000));
            Cookie::queue('ZLINK_USERINFO', json_encode($data['data']['user']), env('USERINFO_EXP', 21600));
        } else {
            $data =  [
                'meta'=>[
                    'code'=>'401',
                    'message'=>'登录失败，账户或密码错误',
                ]
            ];
            //登录失败次数Redis记录
            $key = 'hades:num:account:'.$params['account'];
            if (Redis::connection('read')->hExists($key, 'login')) {
                Redis::connection('write')->hincrby($key, 'login', 1);
            } else {
                //如果 key 不存在，一个新的哈希表被创建并执行 HINCRBY 命令
                Redis::connection('write')->hincrby($key, 'login', 1);
                Redis::connection('write')->expire($key, Date::today()->add(env('LOGIN_NUM_DAY', 1).' day')->timestamp - time());
            }
        }

        $cookie=$request->cookie();

        return response()->json($data);


    }

    /**
     * 退出
     */
    public function logout(Request $request)
    {
//        Cookie::queue(Cookie::forget('ZLINK_TK'));
//        Cookie::queue(Cookie::forget('ZLINK_NAME'));
//        Cookie::queue(Cookie::forget('ZLINK_USERINFO'));
//        return redirect('http://127.0.0.1:8088');
////        //获取token
////        $params['token'] = Cookie::get('ZLINK_TK');
////
////        $data = User::out($params);
////        if($data['meta']['code'] == 200){
////            Cookie::queue(env('ZLINK_TK', 'ZLINK_TK'), null, -1);
////            Cookie::queue(env('ZLINK_USERINFO', 'ZLINK_USERINFO'), null, -1);
////        }
////        return redirect('http://127.0.0.1:8080');

        //获取token
        $params['token'] = Cookie::get('ZLINK_TK');
        //退出操作

        $Jwt2= $this->getJwtDecode($params);

        $jwt = $Jwt2['encode'];
        $r = User::delJwt($jwt);
        if($r) {
            Cookie::queue(env('ZLINK_TK', 'ZLINK_TK'), null, -1);
            Cookie::queue(env('ZLINK_USERINFO', 'ZLINK_USERINFO'), null, -1);
            $server=$request->server();
            if (isset($server['HTTP_REFERER'])) {
                $referer = $server['HTTP_REFERER'];
            } else {
                $referer = changeUrl(route('login'));
            }
            return redirect($referer);

        }
        return $this->returnCode(401);


    }

    /**退出登录的具体实现
     * @param $params
     * @return array
     */
    public function getJwtDecode($params)
    {
        $token = $params['token'];
        //读取redis里面的jwt是否有token
        if(User::isJwtExists($token) != 1) {
//            \Log::error(__METHOD__.'=>token not exists or expire');
            return false;
        }

        $Jwt2['encode'] = $token;

        //解码token
        $Jwt2['decode'] = (array)JWT::decode($token, env('JWT_KEY', ''), array('HS256'));

        return $Jwt2;
    }

    /**
     * 忘记密码页面
     */
    public function forget()
    {
        return view('sign.forget');
    }

    /**
     * 手机重置密码身份验证,点击重置按钮执行
     * @param  Request $request [description]
     * @return [type] [description]
     */
    public function phoneReset(Request $request)
    {
        $params = $request->only('phone', 'code');
        $params['phone'] = strtolower(trim($params['phone']));
        $params['code'] = strtolower(trim($params['code']));

        $validator = Validator::make($params, [
            'phone' => 'required|phone:CN',
            'code' => 'required|max:6',
        ]);
        if ($validator->fails()) {
            return [
                'meta'=>[
                    'code'=>'400',
                    'message'=>$validator->errors()->all(),
                ]
            ];
        }

        $params['agent'] = Agent::getUserAgent();
        $params['ip'] = getClientIp();

        $data = $this->sendResetPhone($params);

        return response()->json($data);
    }

    /**
     * 发送重置密码邮件
     * @param  Request $request [description]
     * @return [type] [description]
     */
    public function sendResetEmail(Request $request)
    {
        $params = $request->only('email','action');
        $params['email'] = strtolower(trim($params['email']));

        $validator = Validator::make($params, [
            'email' => 'required|email|max:90',
        ]);
        if ($validator->fails()) {
            return [
                'meta'=>[
                    'code'=>'400',
                    'message'=>$validator->errors()->all(),
                ]
            ];
        }

        $params['agent'] = Agent::getUserAgent();
        $params['ip'] = getClientIp();

        $data = $this->emailConfirmSend($params);

        return response()->json($data);
    }

    /**
     * 密码重置页面,通过邮箱链接进入的页面
     * @param  Request $request [description]
     * @return [type] [description]
     */
    public function resetView(Request $request, $code)
    {
        $params = [
            'code' => strtolower(trim($code)),
            'agent' => Agent::getUserAgent(),
            'ip' => getClientIp(),
        ];

        //验证code是否正确
        $data = $this->checkResetMail($params);

        if ($data['meta']['code'] == 200) {
            //code正确显示填写密码界面
            return view('sign.reset', compact('code'));
        } else {
            $info = [
                'title' => '重置密码链接无效！',
                'content' => [],
                'href' => changeUrl(route('home')),
                'a' => '点击返回主页。',
            ];
//            return view('common._info', compact('info'));
        }
    }

    /**
 * 重置密码页面,验证码通过后才可以进入重置页面
 * @param  [type]   $params [description]
 * @return [type]           [description]
 */
    public function checkResetMail($params)
    {
        $key = 'apollo:code:reset:'.$params['code'];
        $r = Redis::connection('read')->hGetAll($key);
        if (!isset($r['code']) || $r['code'] != $params['code']) {
            return $this->returnCode(400, '', ['重置密码链接地址错误']);
        }

        return $this->returnCode(200);
    }


    /**
     * 重置密码,新密码符合规范才可以重置密码
     * @param  [type]   $params [description]
     * @return [type]           [description]
     */
    public function reset(Request $request)
    {
        $params = $request->only('password', 'password_confirmation', 'code');
        $params['code'] = strtolower(trim($params['code']));

//        $validator = Validator::make($params, [
//            'password' => 'required|confirmed|min:6|max:32',
//            'code' => 'required|size:32',
//        ]);
//        if ($validator->fails()) {
//            return $this->returnCode(400, '', $validator->errors()->all());
//        }

        //通过code定义$key这个路径
        $key = 'apollo:code:reset:'.$params['code'];
        //读取$key的具体值
        $r = Redis::connection('read')->hGetAll($key);
        //验证code是否正确
        if (!isset($r['code']) || $r['code'] != $params['code']) {
            return $this->returnCode(400, '', ['重置密码链接地址错误']);
        }

        $r['password'] = $params['password'];
        $data = User::resetPassword($r);
        $user=$data->toArray();


        $params['agent'] = Agent::getUserAgent();
        $params['ip'] = getClientIp();

        //写日志
        $info = [
            'theme' => 'user',
            'title' => 'reset_password',
            'info' => [
                'uid'=>intval($data['user_id']),
                'controller'=>'sign',
                'action'=>'resetPassword',
                'desc' => '修改密码成功',//日志描述
                'add_time' => time(),
                'ip' => $params['ip'],
                'agent' => $params['agent'],
            ]
        ];
        event(new LogEvent($info));

        if ($data) {
            //成功后自动登录
            //SignInEvent事件做以下事情，生成jwt,把jwt写入redis
            $data = event(new SignInEvent($user))[0];

            //写登录日志
            $info = [
                'theme' => 'user',
                'title' => 'sign_in',
                'info' => [
                    'uid'=>intval($user['user_id']),
                    'controller'=>'sign',
                    'action'=>'postIn',
                    'desc' => '登录成功',//日志描述
                    'add_time' => time(),
                    'ip' => $params['ip'],
                    'agent' => $params['agent'],
                ]
            ];
            event(new LogEvent($info));

            $data['user'] = $user;

            $data['data']['user'] = $user;

            //登录成功，写入Cookie
            if(isset($data['data']['token'])){
                Cookie::queue('ZLINK_TK', $data['data']['token'], env('JWT_EXP', 21600));
//                Cookie::queue('ZLINK_NAME', $params['phone'], env('NAME_EXP', 54000));
                Cookie::queue('ZLINK_USERINFO', json_encode($data['user']), env('USERINFO_EXP', 21600));
            } else {
                $data =  [
                    'meta'=>[
                        'code'=>'401',
                        'message'=>'登录失败，账户或密码错误',
                    ]
                ];
            }

            return $this->returnCode(200,'',$data);
        } else {
            return $this->returnCode(500);
        }
    }





}

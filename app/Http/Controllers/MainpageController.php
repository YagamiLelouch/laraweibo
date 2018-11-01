<?php

namespace App\Http\Controllers;

use App\Model\Articles;
use App\Model\Collection;
use App\Model\Images;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use Redirect, Response;
use App\Model\User;
use Redis;
use Jenssegers\Date\Date;
use \Firebase\JWT\JWT;
use App\Events\SignInEvent;
use App\Events\LogEvent;
use App\Jobs\SendEmail;
use Cookie;
use Agent;
use Storage;
use Image;
class MainpageController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt', ['only' => ['index', 'publish', 'upload']]);
    }
    /**
     * 显示主页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
//        dd($request->noToken);
        //获取请求数据
        $params = $request->only('page', 'limit');
        //读取数据
        $data = Articles::getArticles();


        $pattern = '/\[(a|b|c|d)_([0-9])+\]/i';
        $replacement = '<img src="'.changeUrl(asset('face/$1/$2.gif')).'" border="0">';
        //遍历出内容,把表情解析
        foreach ($data as $key => $value) {
            $str = $value['content'];
            $data[$key]['content'] = preg_replace($pattern, $replacement, $str);
//            $data[$key]['content'] = preg_replace('/\[(a|b|c|d)_([0-9])+\]/i', '<img src="'.changeUrl(asset('face/$1/$2.gif')).'" border="0">', $str);
        }


        //获取用户收藏
        if (User::isLogin()) {
            $user_id = $request->jwt['decode']['data']->user_id;
            $coll = Collection::getCollection($user_id);
            if ($coll) {
                foreach ($coll as $v) {
                    $colls[] = $v['article_id'];
                }
                $collections = array_flip($colls);
            } else {
                //获取用户收藏文章id的信息
                $collections = [];
            }

        } else {
            //获取用户收藏文章的id信息
            $collections = [];
        }


        //定义page数组,链接中没有就默认为1
        $pages['page'] = $params['page'] ? $params['page'] : 1;
        //定义页面限制数据10条
        $pages['limit'] = 5;
        //将数组变成集合对象
        $data = collect($data);
        //定义页面中所有数据条数
        $pages['count'] = $data->count();
        //定义总页数
        $pages['pages'] = ceil($pages['count'] / 5);
        //定义页面的url
        $pages['url'] = route('home', ['page' => '']);
        //定义一次性显示10个翻页按钮
        $pages['num'] = 5;
        //forPage 方法返回给定页码上显示的项目的新集合。这个方法接受页码作为其第一个参数和每页显示的项目数作为其第二个参数。用来限制页面的显示信息
        $data = $data->forPage($pages['page'], $pages['limit']);

        return view('mainpage.home', compact('data', 'pages', 'collections'));
    }

    //发表内容
    public function publish(Request $request)
    {
        $params = $request->only('content');

        $validator = \Validator::make($params, [
            'content' => 'required|min:1|max:240'
        ]);

        //判断验证
        if ($validator->fails()) {
            return $this->returnCode(400, '', $validator->errors()->all());
        }
//        $params['user_id']=$request->jwt['decode']['data']->user_id;
        $params['user_id'] = $request->jwt['decode']['data']->user_id;

        //内容写入数据库
        $data = Articles::publish($params);
        if ($data) {
            return $this->returnCode(200);
        } else {
            return $this->returnCode(401);
        }
    }

    /**
     * 文件上传
     */
    public function upload(Request $request)
    {
//        //计算有多少个文件
//        $total = count($_FILES['files']['name']);
//
//
//
////循环获取每个文件的各个字段的数据
//        for ($i = 0; $i < $total; $i++) {
//            $fileName = $_FILES["files"]["name"][$i]; // The file name
//            $fileTmpLoc = $_FILES["files"]["tmp_name"][$i]; // File in the PHP tmp folder
//            $fileType = @$_FILES["files"]["image/png||image/jpg"][$i];  // The type of file it is
//            $fileSize = $_FILES["files"]["size"][$i]; // File size in bytes
//            $fileErrorMsg = $_FILES["files"]["error"][$i]; // 0 = false | 1 = true
//            $kaboom = explode(".",$_FILES["files"]["name"][$i]); // Split file name into an array using the dot
//            $fileExt = end($kaboom); // Now target the last array element to get the file extension
//
////            //将临时保存的文件移动到自己所指定的位置和指定的文件名
////            $moveResult= move_uploaded_file($fileTmpLoc, "uploads/aaa.$fileExt.$fileName");
////            unlink($fileTmpLoc); // Remove the uploaded file from the PHP temp folder
////            echo $fileName.'<br>';
//
//            $bool = Storage::disk('uploads')->put($fileName, file_get_contents($fileTmpLoc));
//        }

        $params['content'] = $request->get('texts');

        $params['user_id'] = $request->jwt['decode']['data']->user_id;

        //内容写入数据库
        $data = Articles::publish($params);
        //返回的数据解析成数组
        $data = json_decode(json_encode($data), true);

        //获取插入content的id值
        $aid = $data['article_id'];

//        if ($data) {
//            return $this->returnCode(200);
//        } else {
//            return $this->returnCode(401);
//        }

        if($request->file('files'))
        {
            foreach($request->file('files') as $file)
            {
                if (!empty($file))
                {
                    //定义文件保存路径
                    $destinationPath = 'uploads/';
                    //获取文件名
                    $filename = $file->getClientOriginalName();
                    //文件移动到指定文件夹(这个方法很强大, 都不需要知道文件的完整路径就可以移动文件)
                    $newName = uniqid().'_'.$filename;
                    $file->move($destinationPath, $newName);

                    //获取原图的文件路径
                    $fileUrl = $destinationPath.$newName;
                    //图片缩小到180并保存
                    $smallImage = Image::make($fileUrl)->resize(180, 180);
                    $smallUrl = $destinationPath.'180_'.$newName;
                    $smallImage->save($smallUrl);
                    //图片缩小到550并保存
                    $bigImage = Image::make($fileUrl)->resize(550, 550);
                    $bigUrl = $destinationPath.'550_'.$newName;
                    $bigImage->save($bigUrl);

                    //图片保存到数据库
                    $data = Images::store($smallUrl, $bigUrl, $fileUrl, $aid);
                    echo $data;

//                    return Response::json(
//                        [
//                            'success' => true,
//                            'avatar' => asset($destinationPath.$newName),
//                        ]
//                    );
////                    $destinationPath = '/';
////                    $filename = $media->getClientOriginalName();
////                    $media->move($destinationPath, $filename);
//
//                    // 获取文件相关信息
//                    $originalName = $media->getClientOriginalName(); // 文件原名
////                    $ext = $media>getClientOriginalExtension();     // 扩展名
//                    $realPath = $media->getRealPath();   //临时文件的绝对路径
//                    $type = $media->getClientMimeType();     // image/jpeg
//
//                    // 上传文件名
//                    $filename = date('Y-m-d-H-i-s') . '-' . uniqid() . '.jpg';
//                    // 使用我们新建的uploads本地存储空间（目录）
////                    $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));

                }
            }
        }


    }
}

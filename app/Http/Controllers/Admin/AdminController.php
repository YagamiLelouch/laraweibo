<?php

namespace App\Http\Controllers\Admin;

use App\Model\Articles;
use App\Model\Field;
use App\Model\Collection;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Http\Response;
use App\Model\User;
use Redis;
use Jenssegers\Date\Date;
use \Firebase\JWT\JWT;
use App\Events\SignInEvent;
use App\Events\LogEvent;
use App\Jobs\SendEmail;
use Cookie;
use Agent;
use JavaScript;
class AdminController extends Controller
{
   public function index(Request $request)
   {
       //获取请求数据
       $params=$request->only('page','limit');
       //读取文章和用户的关联数据
       $data=Articles::getArticles();

       //获取所有用户数据
       $userInfo = User::allInfo();


       //获取数据表字段数据库(article)
       $fieldArticle =Field::getField(1);
//       dd($fieldArticle);
       //获取数据表字段数据库(user)
       $fieldUser =Field::getField(2);

       JavaScript::put([
           'fieldArticle' => $fieldArticle,
           'fieldUser' => $fieldUser,
           'info' => $data,
           'userInfo' => $userInfo
       ]);

       ///定义page数组,链接中没有就默认为1
       $pages['page'] = $params['page'] ? $params['page'] : 1;
       //定义页面限制数据10条
       $pages['limit'] = 5;
       //将数组变成集合对象
       $data = collect($data);
       //定义页面中所有数据条数
       $pages['count'] = $data->count();
       //定义总页数
       $pages['pages']=ceil($pages['count']/5);
       //定义页面的url
       $pages['url'] = route('home', [ 'page' => '']);
       //定义一次性显示10个翻页按钮
       $pages['num'] = 4;
       //forPage 方法返回给定页码上显示的项目的新集合。这个方法接受页码作为其第一个参数和每页显示的项目数作为其第二个参数。用来限制页面的显示信息
//       $data = $data->forPage($pages['page'], $pages['limit']);
//       return view('mainpage.home',compact('data','pages'));

       ///定义page数组,链接中没有就默认为1
       $pagesU['page'] = $params['page'] ? $params['page'] : 1;
       //定义页面限制数据2条
       $pagesU['limit'] = 3;
       //将数组变成集合对象
       $dataU = collect($userInfo);
       //定义页面中所有数据条数
       $pagesU['count'] = $dataU->count();
       //定义总页数
       $pagesU['pages']=ceil($pagesU['count']/3);
       //定义页面的url
       $pagesU['url'] = route('home', [ 'page' => '']);
       //定义一次性显示10个翻页按钮
       $pagesU['num'] = 3;
       return view('admin.article',compact('pages', 'pagesU','fieldArticle', 'fieldUser'));
   }

   public function info(Request $request)
   {
       //获取请求数据
       $params=$request->only('page','limit');
       //读取article数据
       $data=Articles::getArticles();

       ///定义page数组,链接中没有就默认为1
       $pages['page'] = $params['page'] ? $params['page'] : 1;
       //定义页面限制数据10条
       $pages['limit'] = 5;
       //将数组变成集合对象
       $data = collect($data);
       //定义页面中所有数据条数
       $pages['count'] = $data->count();
       //定义总页数
       $pages['pages']=ceil($pages['count']/5);
       //定义页面的url
       $pages['url'] = route('home', [ 'page' => '']);
       //定义一次性显示10个翻页按钮
       $pages['num'] = 4;
       //forPage 方法返回给定页码上显示的项目的新集合。这个方法接受页码作为其第一个参数和每页显示的项目数作为其第二个参数。用来限制页面的显示信息
       $data = $data->forPage($pages['page'], $pages['limit']);
       return json_decode($data);
//       return view('mainpage.home',compact('data','pages'));
//       return view('admin.article',compact('data','pages'));

   }

   public function userAll(Request $request)
   {
       //获取请求数据
       $params=$request->only('page','limit');
       //获取所有用户数据
       $data = User::allInfo();

       ///定义page数组,链接中没有就默认为1
       $pagesU['page'] = $params['page'] ? $params['page'] : 1;
       //定义页面限制数据2条
       $pagesU['limit'] = 3;
       //将数组变成集合对象
       $dataU = collect($data);
       //定义页面中所有数据条数
       $pagesU['count'] = $dataU->count();
       //定义总页数
       $pagesU['pages']=ceil($pagesU['count']/3);
       //定义页面的url
       $pagesU['url'] = route('home', [ 'page' => '']);
       //定义一次性显示10个翻页按钮
       $pagesU['num'] = 3;
       //forPage 方法返回给定页码上显示的项目的新集合。这个方法接受页码作为其第一个参数和每页显示的项目数作为其第二个参数。用来限制页面的显示信息
       $data = $dataU->forPage($pagesU['page'], $pagesU['limit']);
       return json_decode($data);
   }
}

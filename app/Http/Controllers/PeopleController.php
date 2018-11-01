<?php

namespace App\Http\Controllers;

use App\Model\Articles;
use Illuminate\Http\Request;
use App\Model\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PeopleController extends Controller
{
    /**显示个人主页
     * @param Request $request
     * @param $username
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request,$username)
    {
        $params = $request->only('page', 'limit');
        //通过username获取到id
        $id=User::getId($username);
        //通过id获取用户发表的内容
        $contents = Articles::userContent($id);
        //分页
        $pages['page'] = $params['page'] ? $params['page'] : 1;
        $pages['limit'] = 5;
        $contents = collect($contents);
        $pages['count'] = $contents->count();
        //定义总页数
        $pages['pages']=ceil($pages['count']/5);
        //定义页面的url
        $pages['url'] = route('people', [ 'page' => '']);
        //定义一次性显示10个翻页按钮
        $pages['num'] = 5;
        $contents = $contents->forPage($pages['page'], $pages['limit']);
        return view('mainpage.people',compact('contents', 'pages', 'username'));
    }

    /**
     * 个人主页内容ajax分页
     */
    public function ajaxContent(Request $request)
    {

    }
}

<?php

namespace App\Http\Controllers;

use App\Model\User;
use Illuminate\Http\Request;
use App\Model\Articles;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->only('type', 'q');
        $params['type'] = $params['type'] ? $params['type'] : 'all';
        if ($params['type'] == 'all') {
            //搜索到所有带有关键词$q的所有微博
            $contents = Articles::searchAll($params['q']);
//            $contents = User::searchAll($q);
        }
        return view('search.show', compact('contents'));
    }
}

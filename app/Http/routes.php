<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/','MainpageController@index')->name('home');//Blog主页
Route::post('/publish','MainpageController@publish')->name('publish');//Blog发布
Route::post('/upload', 'MainpageController@upload')->name('upload');//文件上传
Route::post('/pComment', 'CommentController@pComment')->name('pComment');//发表评论
Route::post('/getComment', 'CommentController@getComment')->name('getComment');//显示评论

Route::get('/people/{username}','PeopleController@index')->name('people');//个人主页

Route::get('/search', 'SearchController@index')->name('search');//搜索

Route::get('/islogin','SignController@isLoginAjax')->name('islogin');//Ajax判断登录状态

Route::put('/addcollection','CollectionController@addCollection')->name('addcolllection');//添加收藏
Route::delete('/deletecollection','CollectionController@deleteCollection')->name('deletecollection');//取消收藏

Route::get('/profile','UserController@profile')->name('profile');//个人资料
Route::post('/edit','UserController@edit')->name('edit');//个人资料修改
Route::get('/password','UserController@password')->name('password');//修改密码界面
Route::post('/change_password','UserController@changePassword')->name('change_password');//修改密码
Route::get('/collection','UserController@collection')->name('collection');//用户收藏

Route::get('/login','SignController@loginView')->name('login');//登录view
Route::post('/login','SignController@login')->name('login');//登录
Route::get('/logout','SignController@logout')->name('logout');//退出登录
Route::get('/islogin','SignController@isLogin')->name('islogin');//Ajax判断登录状态

Route::get('/signup','SignController@signUpView')->name('signup');//用户注册view
Route::post('/signup','SignController@signUp')->name('signup');//用户注册
Route::get('/email_signup','SignController@emailUp')->name('email_signup');//邮箱注册确认
Route::post('/sendCode','SignController@sendCode')->name('sendCode');//手机注册/重置发送验证码

Route::get('/forget','SignController@forget')->name('forget');//重置密码填写手机/邮箱页面
Route::post('/reset/email', 'SignController@sendResetEmail')->name('reset.email');//发送重置邮件
Route::post('/reset/phone', 'SignController@phoneReset')->name('reset.phone');//手机重置确认
Route::get('/password/reset/{code}', 'SignController@resetView')->name('password.reset');//重置页面
Route::post('/password/reset', 'SignController@reset')->name('password.update');//重置密码

require_once "adminRoutes.php";



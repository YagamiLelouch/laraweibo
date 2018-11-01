<?php
/**
 * Created by PhpStorm.
 * User: shaowen.wang
 * Date: 2018/3/6
 * Time: 10:39
 */

Route::get('/admin','Admin\AdminController@index')->name('admin');//管理员页面
Route::post('/info','Admin\AdminController@info')->name('info');//ajax获取分页内容
Route::post('/userAll','Admin\AdminController@userAll')->name('userAll');//ajax获取user信息的分页内容
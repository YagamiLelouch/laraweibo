<?php
/**
 * Created by PhpStorm.
 * User: crabhw
 * Date: 2017/8/3
 * Time: 9:12
 */

/**
 * 改变 URL 主机地址
 */
function changeUrl($url) {

    $data = str_replace(config('app.url'), config('layouts.host'), $url);

    return $data;
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name) {
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示最后一个）
 * @param string $str
 * @param int $ll  左边长度
 * @param int $rl  右边长度
 * @param string $len  是否显示长度，hide隐藏位数，show显示位数
 * @return string 格式化后的str
 */
function hide_str($str, $ll = 1, $rl = 1, $len = 'hide') {
    $strlen = mb_strlen($str, 'utf-8');
    $lStr   = mb_substr($str, 0, $ll, 'utf-8');
    $rStr   = mb_substr($str, -1, $rl, 'utf-8');
    if ($len == 'show') {
        return $strlen <= ($ll+$rl) ? '*'.$rStr : $lStr.str_repeat("*", $strlen - ($ll+$rl)).$rStr;
    } else {
        return $strlen <= ($ll+$rl) ? '*'.$rStr : $lStr."*".$rStr;
    }
}

/**
 * 多维数组转一维数组且K值转为大写
 * @param array $array
 * @return array 格式化后的array
 */
function array2dot($array) {
    static $data = [];
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            array2dot($v);
        } else {
            $data[strtoupper($k)] = $v;
        }
    }
    return $data;
}

/**
 * 获取客户端ip
 */
function getClientIP() {
    Request::setTrustedProxies(['172.18.135.34', '172.18.135.37']);
    return Request::getClientIP();
}

function getip(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}


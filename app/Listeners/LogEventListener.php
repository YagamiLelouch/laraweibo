<?php

namespace App\Listeners;

use App\Events\LogEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogEventListener
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
     * @param  LogEvent  $event
     * @return void
     */
    public function handle(LogEvent $event)
    {
        $data = $event->data;
        return $this->writeLog($data);
    }


    /**
     * 写入日志
     * @param  array $params 日志参数
     * @return string
     */
    private function writeLog($params)
    {
        $theme = isset($params['theme']) ? $params['theme'] : 'Default_Theme';
        $title = isset($params['title']) ? $params['title'] : 'Default_Title';
        $path = isset($params['path']) ? $params['path'] : storage_path('logs/'.$theme.'/'.$title.'.log');
        $info = isset($params['info']) ? $params['info'] : [];

        $log = new \Illuminate\Log\Writer(new \Monolog\Logger($theme));
        $log->useFiles($path);
        $log->info($title, $info);

        return [
            'meta'=>[
                'code'=>'200',
                'message'=>'log write success'
            ],
            'data'=>[
                'path'=>$path,
            ],
        ];
        
    }

    /**
     * 获取日志文件路径
     * @param [type] $title [description]
     * @param [type] $theme [description]
     */
    private function getPath($theme, $title)
    {
        return storage_path('logs/'.$theme.'/'.$title.'.log');
    }


}

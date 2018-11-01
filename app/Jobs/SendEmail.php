<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Http\Request;

class SendEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
         $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $this->sendEmail($this->data);
    }

    private function sendEmail($data)
    {
        $validator = \Validator::make($data, [
            'view' => 'required|min:1|max:255',//邮件模板
            'subject' => 'required|max:255',//邮件主题
            'email' =>'required|email',
        ]);
        if ($validator->fails()) {
            $error_info = __METHOD__.":邮件参数验证失败!";
            \Log::error($error_info);
            echo $error_info."\r\n";
            return false;
        }

        //send 方法接收三个参数。首先是包含邮件消息的 视图 名称。其次是一个要传递给该视图的数据数组。最后是一个用来接收消息实例的 闭包回调，让你可以自定义收件者、主题，以及邮件消息的其它部分
        \Mail::send('emails.'.$data['view'], ['data' => $data], function ($m) use ($data) {
            //设置接收方信息,strstr — 查找字符串的首次出现,subject代表主题
            $m->to($data['email'], strstr($data['email'], '@', true))->subject($data['subject']);
        });
    }


}

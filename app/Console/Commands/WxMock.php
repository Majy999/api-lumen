<?php

namespace App\Console\Commands;

use App\Helpers\Tools;
use App\Services\MinaService;
use Hanson\Vbot\Foundation\Vbot as Bot;
use Hanson\Vbot\Message\Text;
use Hanson\Vbot\Message\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use EasyWeChat\Kernel\Messages\Raw;

class WxMock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wxmock {session=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '模拟微信操作';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('vbot');

        $session = $this->argument('session');
        if ($session) {
            $config['session'] = $session;
        }

        $vbot = new Bot($config);

        // 获取消息处理器实例
        $messageHandler = $vbot->messageHandler;

        // 收到消息时触发
        $messageHandler->setHandler(function (Collection $message) {

            // 接收发送消息的好友
            $friends = vbot('friends');

            if ($message['type'] === 'new_friend') {
                vbot('console')->log('new_friend', '自定义日志');
                Text::send($message['from']['UserName'], '感谢你这么好看还加更好看的我');
                Image::send($message['from']['UserName'], env('ROOT_PATH') . '/public/image/0.png');
                vbot('console')->log('发送图片', '自定义日志');
            }

            if ($message['type'] === 'request_friend') {
                vbot('console')->log('request_friend', '自定义日志');
                vbot('console')->log('收到好友申请:' . $message['info']['Content'] . $message['avatar']);
                // 同意添加为好友
                $friends->approve($message);
            }

            Text::send($message['from']['UserName'], '默认回复');
        });

        // 获取监听器实例
        $observer = $vbot->observer;

        // 二维码监听器
        $observer->setQrCodeObserver(function ($qrCodeUrl) use ($session) {

            // 文件保存目录
            $fileDir = env('ROOT_PATH', __DIR__) . '/public/image/qrcode/';
            if (!is_dir($fileDir)) @mkdir($fileDir, 755, true);
            $fileName = $session . '.jpg';

            $qrCodeUrl = str_replace('/l/', '/qrcode/', $qrCodeUrl);
            vbot('console')->log($qrCodeUrl, '程序退出');

            // 保存图片
            $ch = curl_init($qrCodeUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            $img = curl_exec($ch);
            curl_close($ch);
            $fp = fopen($fileDir . $fileName, 'w');
            fwrite($fp, $img);
            fclose($fp);

            // 发送客服消息
            $title = '集客';
            $logo = 'https://img.jkweixin.com/defaults/b-image/page/icon-login-logo@2x.png';
            $url = 'https://api.majy999.com/login-wxmock?session=' . $session;
            $message = new Raw('{
                        "touser": "' . $session . '",
                        "msgtype": "link",
                        "link": {
                              "title": "' . $title . ': 请求登录",
                              "description": "请求扫码登录",
                              "url": "' . $url . '",
                              "thumb_url": "' . $logo . '"
                        }
                  }');
            // 回复消息
            $minaService = new MinaService();
            $result = $minaService->customerServerSend($message, $session);

            // 打印错误日志
            if (!$result) {
                Tools::logInfo(print_r($result, 1));
            }
        });

        // 登录成功监听器
        $observer->setLoginSuccessObserver(function () {

        });

        // 免扫码成功监听器
        $observer->setReLoginSuccessObserver(function () {

        });

        // 程序退出监听器
        $observer->setExitObserver(function () {
            vbot('console')->log('程序退出监听器', '程序退出监听器');
        });

        // 好友监听器
        $observer->setFetchContactObserver(function (array $contacts) {
//            print_r($contacts['friends']);
//            print_r($contacts['groups']);
        });

        // 消息处理前监听器
        $observer->setBeforeMessageObserver(function () {

        });

        // 异常监听器
        $observer->setNeedActivateObserver(function () {
            vbot('console')->log('异常监听器', '异常监听器');
        });

        $vbot->server->serve();
    }
}

<?php

class WeChatRobot
{
    private static $ins;

    private $key;

    private const WECHAT_ROBOT_URL = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send";
    private const WECHAT_ROBOT_UPLOAD_URL = "https://qyapi.weixin.qq.com/cgi-bin/webhook/upload_media?key=%s&type=file";
    private const WECHAT_ROBOT_UPLOAD_MAX_SIZE = 20971520;

    public static function get($type = ""){
        if(null == self::$ins){
            self::$ins = new WeChatRobot();
            self::$ins->setRobot($type);
        }
        return self::$ins;
    }

    public function setRobot($type){
        $key = Config::get("wechatrobot.$type") ?? "";
        DBC::assertNotEmpty($key, "[WeChatRobot Exception] Unknow Robot Type ".$type);
        $this->key = $key;
    }

    private function send($data, $msgType = "text"){
        $ezCurl = new EzCurl();
        $ezCurl->setUrl(self::WECHAT_ROBOT_URL."?key=".$this->key."&debug=1");
        $body = [
            "msgtype"=>$msgType,
            $msgType => $data
        ];
        $ezCurl->setHeader([
            "Content-type: application/json;Charset=UTF-8",
            "Expect:"
        ]);
        return $ezCurl->post($body, EzCurl::POSTTYPE_JSON);
    }

    /**
     * @param $content          @文本内容，最长不超过2048个字节，必须是utf8编码
     * @param array $at         userid的列表，提醒群中的指定成员(@某个成员)，@all表示提醒所有人，如果开发者获取不到userid，可以使用mentioned_mobile_list
     * @param array $atmobiles  手机号列表，提醒手机号对应的群成员(@某个成员)，@all表示提醒所有人
     * @return array|bool|string
     */
    public function sendText($content, $atmobiles = [], $at = []){
        foreach($at as &$item){
            $item = EzString::convertToUnicode($item);
        }
        return $this->send(['content' => EzString::convertToUnicode($content), "mentioned_list" => $at, 'mentioned_mobile_list' => $atmobiles]);
    }


    public function sendImage($filePath){
        $base64 = EzEncoder::imgBase64EncodeSimple($filePath);
        return $this->send(["md5" => md5_file($filePath), 'base64' => $base64], "image");
    }

    /**
     * @param $title        @标题，不超过128个字节，超过会自动截断
     * @param $description  @描述，不超过512个字节，超过会自动截断
     * @param $jumpUrl      @点击后跳转的链接。
     * @param $picUrl       @图文消息的图片链接，支持JPG、PNG格式，较好的效果为大图 1068*455，小图150*150。
     * @return array|bool|string
     */
    public function sendNewsOne($title, $description, $jumpUrl, $picUrl){
        return $this->send(['articles' => [['title' => EzString::convertToUnicode($title), 'description' => EzString::convertToUnicode($description), 'url' => $jumpUrl, "picurl" => $picUrl]]], 'news');
    }

    public function sendNews($articles){
        return $this->send(['articles' => $articles], 'news');
    }

    public function sendMarkDown($text){
        return $this->send(['content' => EzString::convertToUnicode($text)], "markdown");
    }

    public function sendFile($filePath) {
        $mediaId = $this->upload($filePath);
        DBC::assertNotEmpty($mediaId, "上传文件失败！");
        return $this->send(["media_id"=>$mediaId], "file");
    }

    private function upload($filePath) {
        DBC::assertNotEmpty($filePath, "传入的文件路径为空！");
        DBC::assertTrue(is_file($filePath), "传入的文件路径有误！".$filePath);
        DBC::assertLessThan(self::WECHAT_ROBOT_UPLOAD_MAX_SIZE, filesize($filePath), "传入文件必须小于20m!");
        $cache = new EzFileCache();
        $key = __METHOD__.$filePath;
        $mediaId = $cache->get($key);
        if (!empty($mediaId)) {
            return $mediaId;
        }
        $url = sprintf(self::WECHAT_ROBOT_UPLOAD_URL, $this->key);
        $curl = new EzCurl();
        $curl->setUrl($url);
        $curl->setTimeOut(60);
        $body = ["media" => new CURLFile($filePath)];
        $res = $curl->post($body, EzCurl::POSTTYPE_FILE);
        $res = EzCollectionUtils::decodeJson($res);
        if (empty($res) || 0 != $res['errcode']) {
            return "";
        }
        $mediaId = $res["media_id"];
        $cache->setEX($key, 86400, $mediaId);
        return $mediaId;
    }
}

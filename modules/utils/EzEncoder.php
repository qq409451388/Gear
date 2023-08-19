<?php
class EzEncoder{
    public static function md5($obj){
        if(is_null($obj)){
            return null;
        }
        if(is_array($obj) || is_object($obj)){
            asort($obj);
            return md5(EzString::encodeJson($obj));
        }
        return md5($obj);
    }

    public static function imgBase64Encode($img = '', $imgHtmlCode = true)
    {
        //如果是本地文件
        if (strpos($img, 'http') === false && !file_exists($img)) {
            return $img;
        }
        //获取文件内容
        $file_content = file_get_contents($img);
        if ($file_content === false) {
            return $img;
        }
        $imageInfo = getimagesize($img);
        $prefiex = '';
        if ($imgHtmlCode) {
            $prefiex = 'data:' . $imageInfo['mime'] . ';base64,';
        }
        return $prefiex . (base64_encode($file_content));
    }

    public static function imgBase64EncodeSimple($picPath){
        return base64_encode(file_get_contents($picPath));
    }

    /**
     * 片base64解码
     * @param string $base64_image_content 图片文件流
     * @return string
     */
    public static function imgBase64Decode($base64_image_content)
    {
        if (empty($base64_image_content)) {
            return '';
        }

        //匹配出图片的信息
        $match = preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result);
        if (!$match) {
            return '';
        }

        //解码图片内容
        $base64_image = str_replace($result[1], '', $base64_image_content);
        $file_content = base64_decode($base64_image);
        $file_type = $result[2];
        //如果没指定目录,则保存在当前目录下
        if (empty($path)) {
            return $file_content;
        }
        return empty($path) ? "" : $file_content;
    }

    /**
     * @main 签名
     * @param $content
     * @param $privateKey
     * @return string
     */
    public static function getRsaSign($content, $privateKey)
    {
        $privateKey = chunk_split($privateKey, 64, "\n");
        $rsaKey = "-----BEGIN RSA PRIVATE KEY-----\n" . $privateKey . "-----END RSA PRIVATE KEY-----";
        $key = openssl_pkey_get_private($rsaKey);
        openssl_sign($content, $signature, $key, "SHA256");
        openssl_free_key($key);
        return base64_encode($signature);
    }
    const OPENSSL_CIPHER = "AES-128-CBC";
    const OPENSSL_CBC_KEY = "0123456789abcdef";
    const OPENSSL_CBC_IV = "abcdef0123456789";

    public static function encrypt($word) {
        if (is_null($word)) {
            return null;
        }
        return openssl_encrypt($word, self::OPENSSL_CIPHER, self::OPENSSL_CBC_KEY, 0, self::OPENSSL_CBC_IV);
    }

    public static function decrypt($wordEncryped) {
        if (is_null($wordEncryped)) {
            return null;
        }
        return openssl_decrypt($wordEncryped, self::OPENSSL_CIPHER, self::OPENSSL_CBC_KEY, 0, self::OPENSSL_CBC_IV);
    }
}

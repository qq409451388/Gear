<?php
class EzEncoder{

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
     * @param bool $save_img    是否保存图片
     * @param string $path  文件保存路径
     * @return bool|string
     */
    public static function imgBase64Decode($base64_image_content = '', $path='')
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
            $path = "/tmp/";
        }
        if (!is_dir($path)) {
            //检查是否有该文件夹，如果没有就创建
            mkdir($path, 0777, true);
        }
        $file_name = uniqid() . ".{$file_type}";
        $filePath = $path . $file_name;
        if (file_exists($filePath)) {
            //有同名文件删除
            @unlink($filePath);
        }
        if (file_put_contents($filePath, $file_content)) {
            return $filePath;
        }
        return '';
    }

    /**
     * @main 签名
     * @param $content * @return string * User: sync * Date: 2020/6/12
     * Time: 3:20 下午
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
}
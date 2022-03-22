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

        //解码图片内容(方法一)
        /*$base64_image = preg_split("/(,|;)/",$base64_image_content);
        $file_content = base64_decode($base64_image[2]);
        $file_type = substr(strrchr($base64_image[0],'/'),1);*/

        //解码图片内容(方法二)
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
}
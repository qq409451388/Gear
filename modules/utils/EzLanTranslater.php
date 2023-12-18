<?php
class EzLanTranslater implements EzHelper
{
    public static function getJsonFromJavaToString($string) {
        if (empty($string)) {
            return null;
        }
        // 移除开始和结束的类名和括号
        $data = trim(preg_replace('/^[^\(]*\(|\)$/', '', $string));

        // 分割字符串为键值对
        $pairs = explode(', ', $data);
        $result = [];
        foreach ($pairs as $pair) {
            // 分割键和值
            list($key, $value) = explode('=', $pair, 2);
            $key = trim($key);
            $value = trim($value);

            // 尝试将数值字符串转换为数值类型
            if (is_numeric($value)) {
                if (strpos($value, '.') !== false) {
                    $value = (float)$value; // 浮点数
                } else {
                    $value = (int)$value; // 整数
                }
            }

            // 处理特殊的科学记数法字符串
            if (preg_match('/^-?\d+\.?\d*E-?\d+$/i', $value)) {
                $value = (float)$value; // 转换为浮点数
            }

            $result[$key] = $value;
        }

        // 将数组转换为JSON
        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
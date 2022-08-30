<?php
class EzString
{
    public const EMPTY_JSON_OBJ = "{}";

    public static function convertToUnicode($str)
    {
        return self::convertToEncoding($str, 'UTF-8');
    }

    public static function convertToGbk($str)
    {
        //  return mb_convert_encoding($str, 'GBK', 'auto');
        return self::convertToEncoding($str, 'GBK');
    }

    public static function convertToEncoding($str, $toEncoding)
    {
        if ((! $str) || empty($str))
        {
            return $str;
        }

        $maybechset = mb_detect_encoding($str, array('UTF-8',  'GBK', 'ASCII', 'EUC-CN',  'CP936', 'UCS-2'));
        if (empty($maybechset))
        {
            $tmpstr = mb_convert_encoding($str, $toEncoding, 'UCS-2');
            $tmpchset = mb_detect_encoding($tmpstr, array('GBK'));
            if (strtoupper($tmpchset) == $toEncoding)
            {
                return $tmpstr;
            }
        }
        else if ($maybechset != $toEncoding)
        {
            return mb_convert_encoding($str, $toEncoding, $maybechset);
        }
        return $str;
    }

    public static function convertArrayToUnicode($var){
        if(is_array($var)){
            foreach($var as $k => $v){
                $var[$k] = self::convertArrayToUnicode($v);
            }
            return $var;
        }
        return self::convertToUnicodeNew($var);
    }

    public static function convertToUnicodeNew($str)
    {
        if(is_bool($str) || is_int($str)) return $str;
        $encodingOrder = ['ASCII', 'CP936', 'GBK', 'UTF-8', 'EUC-CN', 'UCS-2'];
        return self::convertToEncodingNew($str, 'UTF-8', $encodingOrder);
    }

    public static function convertToGbkNew($str)
    {
        if(is_bool($str) || is_int($str)) return $str;
        $encodingOrder = ['UTF-8', 'ASCII', 'CP936', 'GBK', 'EUC-CN', 'UCS-2'];
        return self::convertToEncodingNew($str, 'GBK', $encodingOrder);
    }

    protected static function convertToEncodingNew($str, $toEncoding, $recognitionArr = NULL)
    {
        if ((! $str) || empty($str))
        {
            return $str;
        }

        $encodingRecArr = ($recognitionArr === NULL) ? ['GBK', 'UTF-8'] : $recognitionArr;
        $maybechset = mb_detect_encoding($str, $encodingRecArr);
        if (empty($maybechset))
        {
            $tmpstr = mb_convert_encoding($str, $toEncoding, 'UCS-2');
            $tmpchset = mb_detect_encoding($tmpstr, array('GBK'));
            if (strtoupper($tmpchset) == $toEncoding)
            {
                return $tmpstr;
            }
        }
        else if ($maybechset != $toEncoding)
        {
            return mb_convert_encoding($str, $toEncoding, $maybechset);
        }
        return $str;
    }

    public static function truncate($string, $length, $postfix = '...')
    {
        $n = 0;
        $return = '';
        $isCode = false;
        $isHTML = false;
        for ($i = 0; $i < strlen($string); $i++)
        {
            $tmp1 = $string[$i];
            $tmp2 = ($i + 1 == strlen($string)) ? '' : $string[$i + 1];
            if ($tmp1 == '<')
            {
                $isCode = true;
            }
            elseif ($tmp1 == '&' && !$isCode)
            {
                $isHTML = true;
            }
            elseif ($tmp1 == '>' && $isCode)
            {
                $n--;
                $isCode = false;
            }
            elseif ($tmp1 == ';' && $isHTML)
            {
                $isHTML = false;
            }
            if (!$isCode && !$isHTML)
            {
                $n++;
                if (ord($tmp1) >= hexdec("0x81") && ord($tmp2) >= hexdec("0x40"))
                {
                    $tmp1 .= $tmp2;
                    $i++;
                    $n++;
                }
            }
            $return .= $tmp1;
            if ($n >= $length)
            {
                break;
            }
        }
        if ($n >= $length)
        {
            $return .= $postfix;
        }
        $html = preg_replace('/(^|>)[^<>]*(<?)/', '$1$2', $return);
        $html = preg_replace("/<\/?(br|hr|img|input|param)[^<>]*\/?>/i", '', $html);
        $html = preg_replace('/<([a-zA-Z0-9]+)[^<>]*>.*?<\/\1>/', '', $html);
        $count = preg_match_all('/<([a-zA-Z0-9]+)[^<>]*>/', $html, $matches);
        for ($i = $count - 1; $i >= 0; $i--)
        {
            $return .= '</' . $matches[1][$i] . '>';
        }
        return $return;
    }

    public static function cntrim($string)
    {
        return trim($string, "��\t\n\r ");
    }

    public static function convertEncoding($arr, $toEncoding, $fromEncoding='', $convertKey=false)
    {
        if (empty($arr) || $toEncoding == $fromEncoding)
        {
            return $arr;
        }
        if (is_array($arr))
        {
            $res = array();
            foreach ($arr as $key => $value)
            {
                if ($convertKey)
                {
                    $key = mb_convert_encoding($key, $toEncoding, $fromEncoding);
                }
                if (is_array($value))
                {
                    $value = self::convertEncoding($value, $toEncoding, $fromEncoding, $convertKey);
                }
                else
                {
                    $value = mb_convert_encoding($value, $toEncoding, $fromEncoding);
                }
                $res[$key] = $value;
            }
        }
        else
        {
            $res = mb_convert_encoding($arr, $toEncoding, $fromEncoding);
        }
        return $res;
    }

    public static function getFormatTime($time)
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        $alltime = floor((time() - $time) / 60);
        if ($alltime < 60) {
            if ($alltime <= 0) $alltime = 1;
            return $alltime . '����ǰ';
        } elseif ($alltime < 60 * 24) {
            return floor($alltime / 60) . 'Сʱǰ';
        } elseif ($alltime < 60 * 24 * 30) {
            return floor($alltime / 1440) . '��ǰ';
        } else {
            return floor($alltime / 43200) . '����ǰ';
        }
    }

    public static function getRandom($len)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);// ���������
        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    public static function array2XML($array, $charset = 'gbk', $needCdata=true, $surRound = 'DOCUMENT')
    {
        $header = "<?xml version='1.0' encoding='".$charset."' ?>\n";
        $body = self::array2XMLBody($array, $needCdata);
        if (false == empty($surRound))
        {
            $body = "<".$surRound.">\n".$body."\n</".$surRound.">";
        }
        return $header.$body;
    }

    public static function array2XMLBody($array, $needCdata=true)
    {
        if(false == is_array($array))
        {
            return array();
        }
        $xml = "";
        foreach($array as $key=>$val)
        {
            if(is_numeric($key))
            {
                foreach( $val as $key2 => $value)
                {
                    if (false == is_numeric($key2))
                    {
                        $xml.="<$key2>";
                    }
                    if ($needCdata)
                    {
                        $xml .= is_array($value)?self::array2XMLBody($value, $needCdata):'<![CDATA['.$value.']]>'."\n";
                    }
                    else
                    {
                        $xml .= is_array($value)?self::array2XMLBody($value, $needCdata):$value."\n";
                    }
                    if (false == is_numeric($key2))
                    {
                        list($key2,)=explode(' ',$key2);
                        $xml.="</$key2>\n";
                    }
                }
            }
            else
            {
                $pre = "<$key>";
                if (is_array($val) && isset($val['@attributes']) && is_array($val['@attributes']) && false == empty($val['@attributes']))
                {
                    $pre = "<$key";
                    foreach ($val['@attributes'] as $attributeName => $attributeValue)
                    {
                        $pre .= " $attributeName='$attributeValue' ";
                    }
                    $pre .= "/>";
                    unset($val['@attributes']);
                    $key = '';
                }
                $xml.=$pre;
                if ($needCdata)
                {
                    $xml.=is_array($val)?self::array2XMLBody($val, $needCdata):'<![CDATA['.$val.']]>';
                }
                else
                {
                    $xml.=is_array($val)?self::array2XMLBody($val, $needCdata):$val;
                }
                if ($key)
                {
                    list($key,)=explode(' ',$key);
                    $xml.="</$key>\n";
                }
            }
        }

        return $xml;
    }

    public static function isEmail($email)
    {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function versionCompare($v1, $v2)
    {
        if(empty($v1))
        {
            return FALSE;
        }
        $l1  = explode('.',$v1);
        $l2  = explode('.',$v2);
        $len = count($l1) < count($l2) ? count($l1): count($l2);
        for ($i = 0; $i < $len; $i++)
        {
            $n1 = $l1[$i];
            $n2 = $l2[$i];
            if ($n1 > $n2)
            {
                return TRUE;
            }
            else if ($n1 < $n2)
            {
                return FALSE;
            }
        }
        if (count($l1) > count($l2)) {
            return true;
        }
        return FALSE;

    }

    //ȫ��ת���
    public static function fixContent2Banjiao($str)
    {
        $arr = array(
            '��' => 'A', '��' => 'B', '��' => 'C', '��' => 'D', '��' => 'E',
            '��' => 'F', '��' => 'G', '��' => 'H', '��' => 'I', '��' => 'J',
            '��' => 'K', '��' => 'L', '��' => 'M', '��' => 'N', '��' => 'O',
            '��' => 'P', '��' => 'Q', '��' => 'R', '��' => 'S', '��' => 'T',
            '��' => 'U', '��' => 'V', '��' => 'W', '��' => 'X', '��' => 'Y',
            '��' => 'Z', '��' => 'a', '��' => 'b', '��' => 'c', '��' => 'd',
            '��' => 'e', '��' => 'f', '��' => 'g', '��' => 'h', '��' => 'i',
            '��' => 'j', '��' => 'k', '��' => 'l', '��' => 'm', '��' => 'n',
            '��' => 'o', '��' => 'p', '��' => 'q', '��' => 'r', '��' => 's',
            '��' => 't', '��' => 'u', '��' => 'v', '��' => 'w', '��' => 'x',
            '��' => 'y', '��' => 'z', '��' => '0', '��' => '1', '��' => '2',
            '��' => '3', '��' => '4', '��' => '5', '��' => '6', '��' => '7',
            '��' => '8', '��' => '9', '��' => ' '
        );

        foreach($arr as $key => $value)
        {
            $str = mb_ereg_replace($key, $value, $str);
        }
        return $str;
    }

    public static function hiddenTelNumber($phone)
    {
        $kindOf = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //�̶��绰
        if ($kindOf == 1)
        {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);

        }
        return  preg_replace('/(1[3456789]{1}[0-9])[0-9]{5}([0-9]{2})/i','$1*****$2',$phone);
    }

    public static function hiddenEmail($email)
    {
        $hiddenStr = '';
        if (self::isEmail($email))
        {
            list($header, $footer) = explode('@', $email);
            $hiddenStr = substr($header, 0, 3)."****@".$footer;
        }
        return $hiddenStr;
    }

    public static function str_replace_once($needle, $replace, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    public static function encodeJson($obj){
        return json_encode($obj, JSON_UNESCAPED_SLASHES) ?? self::EMPTY_JSON_OBJ;
    }

    public static function _dump(array $arr, $pos = 'default', $critical = []){
        $str = '';
        $eolHash = [
            'html' => '<br/>',
            'default' => "\n"
        ];
        $criticalHash = [
            'html' => '<font color="red" size="4" face="verdana">{temp}</font>',
            'default' => '[{$temp}]'
        ];
        $eol = $eolHash[$pos];
        foreach($arr as $k => $v){
            if(in_array($k, $critical)){
                $k = str_replace('{temp}', $k, $criticalHash[$pos]);
            }
            $str .= '['.$k.']  =>  '.$v.$eol;
        }
        return $str;
    }

    public static function camelCase($str, $speartor, $type = 1){
        $newStr = '';
        $strArr = explode($speartor, $str);
        foreach($strArr as $s){
            $newStr .= ucfirst(strtolower($s));
        }
        return $type == 1 ? lcfirst($newStr) : $newStr;
    }

}

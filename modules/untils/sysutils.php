<?php
class SysUtils
{
    public static function mem(bool $withUnUse = false){
        return self::convert(memory_get_usage($withUnUse));
    }

    public static function memPeak(bool $withUnUse = false){
        return self::convert(memory_get_peak_usage($withUnUse));
    }

    public static function convert(int $byte, int $precision = 2){
        if($byte < 1024){
            return $byte."byte";
        }elseif($byte < 1048576){
            return round($byte/1024, $precision)."KB";
        }elseif($byte < 1073741824){
            return round($byte/1048576, $precision)."MB";
        }else{
            return round($byte/1073741824, $precision)."GB";
        }
    }
}
<?php

class EzObjectUtils
{
    public static function hashCode($obj)
    {
        $hashCode = 0;
        if (is_null($obj)) {
            return $hashCode;
        }
        $str = serialize($obj);
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $h = $hashCode << 5;
            $h -= $hashCode;
            $h += ord($str[$i]);
            $hashCode = $h;
            $hashCode &= 0xFFFFFF;
        }
        return $hashCode;
    }
}

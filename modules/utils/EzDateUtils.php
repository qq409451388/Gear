<?php

class EzDateUtils
{
    private const DAY_SEC = EzDate::DAY_SEC;
    private const HOUR_SEC = EzDate::HOUR_SEC;
    private const MINUTE_SEC = EzDate::MINUTE_SEC;

    private static $dateConfig = [
        ['sec'=>self::DAY_SEC, "desc" => "天"],
        ['sec'=>self::HOUR_SEC, "desc" => "小时"],
        ['sec'=>self::MINUTE_SEC, "desc" => "分钟"],
        ['sec'=>1, "desc" => "秒"],
    ];

    const LEVEL_DAY = 0;
    const LEVEL_HOUR = 1;
    const LEVEL_MIN = 2;

    /**
     * 将秒数转换为中文描述
     * @param $seconds int 时间长度（单位：秒）
     * @param $level  string 转换精确度（默认：秒级） {@link DateTimeUtil::LEVEL_MIN}
     * @return string 时间秒数转换后的中文时间描述 {@example 2小时30分钟33秒}
     * @author guohan
     */
    public static function convertDateTimeToCn($seconds, $level = null)
    {
        $resultStr = "";

        if(!is_null($level) && $seconds < self::$dateConfig[$level]['sec']){
            return "不到1".self::$dateConfig[$level]['desc'];
        }
        foreach(self::$dateConfig as $levelTmp => $item){
            if(!is_null($level) && $level < $levelTmp){
                break;
            }
            if($item['sec'] <= $seconds){
                $num = intval($seconds/$item['sec']);
                $resultStr .= $num.$item['desc'];
                $seconds -= $num * $item['sec'];
            }
        }
        return $resultStr;
    }

    public static function isValid($data) {
        return false !== strtotime($data);
    }
}

<?php
class EzDate{
    private $timeStamp;

    public const DAY_SEC = 86400;
    public const HOUR_SEC = 3600;
    public const MINUTE_SEC = 60;

    const FORMAT_DATETIME = 'Y-m-d H:i:s';
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_TIME = 'H:i:s';

    private static $formatList = [
        self::FORMAT_DATETIME,
        self::FORMAT_DATE,
        self::FORMAT_TIME
    ];

    private function __construct($timeStamp = null){
        $this->timeStamp = is_null($timeStamp) ? time() : $timeStamp;
    }

    public static function now(){
        return new EzDate();
    }

    public static function new($timeStamp) {
        return new EzDate($timeStamp);
    }

    public static function newFromString($dateString) {
        return new EzDate(strtotime($dateString));
    }

    public function formatDate($format){
        return date($format, $this->timeStamp);
    }

    public function dateString(){
        return $this->formatDate(self::FORMAT_DATE);
    }

    public function datetimeString() {
        return $this->formatDate(self::FORMAT_DATETIME);
    }

    /**
     * @deprecated
     * @return false|string
     */
    public function toString(){
        return $this->formatDate(self::FORMAT_DATETIME);
    }

    public function offsetDay(int $day){
        $this->timeStamp += $day*self::DAY_SEC;
        return $this;
    }

    public function offsetHour(int $hour){
        $this->timeStamp += $hour*self::HOUR_SEC;
        return $this;
    }

    public function offsetMinute(int $min){
        $this->timeStamp += $min*self::MINUTE_SEC;
        return $this;
    }

    public function offsetSec(int $sec){
        $this->timeStamp += $sec;
        return $this;
    }

    public static function analyseDateTimeByMin($s, $e, $step){
        return self::analyseDateTime($s, $e, '+'.$step.' minute');
    }
    public static function analyseDateTimeByDay($s, $e){
        return self::analyseDateTime($s, $e, '+1 day');
    }
    public static function analyseDateTimeByYear($s, $e){
        return self::analyseDateTime($s, $e, '+1 year');
    }
    public static function analyseDateTimeByMonth($s, $e){
        return self::analyseDateTime($s, $e, '+1 month');
    }
    public static function analyseDateTimeByHour($s, $e){
        return self::analyseDateTime($s, $e, '+1 hour');
    }

    private static function analyseDateTime($s, $e, $t){
        $date1 = self::formatDateTime($s);
        $e = self::formatDateTime($e);
        $res = [];
        do{
            $date2 = date(self::FORMAT_DATETIME, strtotime($t, strtotime($date1)));
            $res[] = $date1;
            if(strtotime($date2) >= strtotime($e)){
                $res[] = $e;
                break;
            }
            $date1 = $date2;
        }while($date1 <= $e);
        return $res;
    }

    public static function formatDateTime($dateTime, $format = self::FORMAT_DATETIME){
        if(empty($dateTime)){
            return "";
        }
        if(!in_array($format,self::$formatList)){
            DBC::throwEx("[EzDate] UnSupport Format Type");
        }
        return date($format, self::getTime($dateTime));
    }

    public static function getTime($dateTime = ""){
        if(empty($dateTime)){
            return time();
        }
        $res = strtotime($dateTime);
        if(!$res){
           DBC::throwEx("[EzDate] UnKnow DateTime $dateTime");
        }
        return $res;
    }
}

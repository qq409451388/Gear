<?php
class SchduleTaskApplication
{
    /**
     * @var Application $application
     */
    private $application;

    private $crontabExpression;
    private $closure;

    private $taskLog;

    private $timestamp;

    /**
     * @description DOW means of Day Of Week (0 - 7) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
     * @var string[]
     */
    private static $keys = [
        "SEC", "MIN", "HOUR", "DAY", "MONTH", "DOW"
    ];

    public function __construct(Application $application) {
        $this->application = $application;
        $this->taskLog = [];
    }

    public function setCrontab($crontabExpression) {
        $this->crontabExpression = trim($crontabExpression);
        return $this;
    }

    public function registLogic(Closure $closure) {
        $this->closure = $closure;
        return $this;
    }

    private function explainTimeExpression() {
        $timeExpression = explode(" ", $this->crontabExpression);
        $timeExpression = array_filter($timeExpression, function ($val) {
            return "" != $val && !is_null($val);
        });
        DBC::assertEquals(count($timeExpression), 6, "[SCHDULE TASK] Crontab expression error!");
        $timeExpression = array_combine(self::$keys, $timeExpression);
    }

    private function timeWheel() {
        list($timestamp, $step) = $this->explainTimeExpression();
        if (count($this->taskLog) < $step) {
            $this->timeWheel();
        }
    }

    private function sleep() {
        $this->timeWheel();
        $firstTask = current($this->taskLog);
        if ($firstTask > ($currentTimeStamp = time())) {
            sleep($firstTask - $currentTimeStamp);
        }
        while (true) {
            if ($firstTask <= ($currentTimeStamp = time())) {
                $this->timestamp = $currentTimeStamp;
                return;
            }
            usleep(1000);
        }
    }

    public function start() {
        while (true) {
            $this->sleep();
            ($this->closure)();
        }
    }
}
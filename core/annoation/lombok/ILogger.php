<?php
interface ILogger
{
    public function logBefore(RunTimeProcessPoint $rpp);
    public function logAfter(RunTimeProcessPoint $rpp);

    public function logException(RunTimeProcessPoint $rpp);
}
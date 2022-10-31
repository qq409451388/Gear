<?php
class DataAppendColumnRule extends AbstractDataAppendRule
{
    /**
     * @var array<string, mixed>
     */
    public $dataLine;

    public function __construct() {
        parent::__construct();
        $this->appendMode = self::MODE_DATALINE;
    }

    public function calc(&$data) {
        if(self::MODE_DATALINE == $this->appendMode){
            foreach($data as &$dataItem) {
                $dataItem+=$this->dataLine;
            }
        }
    }
}
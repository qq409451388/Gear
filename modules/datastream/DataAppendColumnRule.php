<?php
class DataAppendColumnRule extends AbstractDataAppendRule
{
    /**
     * @var array<string, mixed>
     */
    private $dataLine;

    /**
     * @var Closure|null $customFunction
     */
    private $customFunction;

    public function __construct() {
        parent::__construct();
        $this->appendMode = DataAppendEnum::MODE_DATALINE;
    }

    public function getDataLine() {
        return $this->dataLine;
    }

    public function setDataLine($dataLine) {
        $this->dataLine = $dataLine;
    }

    /**
     * @return Closure|null
     */
    public function getCustomFunction(): ?Closure
    {
        return $this->customFunction;
    }

    /**
     * @param Closure|null $customFunction
     */
    public function setCustomFunction(?Closure $customFunction): void
    {
        $this->customFunction = $customFunction;
    }
}
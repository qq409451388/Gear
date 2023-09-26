<?php
abstract class DataStreamCommand
{
    /**
     * @var Closure $closure
     */
    protected $closure;

    protected $isApplyToItem = false;

    protected $isMultiStream = false;

    public function setLogic(Closure $closure) {
        $this->closure = $closure;
    }

    public function run(&$data) {
        $data = ($this->closure)($data);
    }

    public function runForDataItem(&$data, $key = null) {
        $data = ($this->closure)($data, $key);
    }

    public function applyToItemOnly() {
        $this->isApplyToItem = true;
    }

    public function isApplyToItem() {
        return $this->isApplyToItem;
    }

    public function isMultiStream(): bool
    {
        return $this->isMultiStream;
    }

    public function setIsMultiStream(bool $isMultiStream): void
    {
        $this->isMultiStream = $isMultiStream;
    }
}
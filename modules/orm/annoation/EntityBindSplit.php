<?php
class EntityBindSplit extends EntityBind
{
    protected $split;
    protected $splitColumn;

    protected $splitModel = 'mod';

    /**
     * @return mixed
     */
    public function getSplit()
    {
        return $this->split;
    }

    /**
     * @param mixed $split
     */
    public function setSplit($split): void
    {
        $this->split = $split;
    }

    /**
     * @return mixed
     */
    public function getSplitColumn()
    {
        return $this->splitColumn;
    }

    /**
     * @param mixed $splitColumn
     */
    public function setSplitColumn($splitColumn): void
    {
        $this->splitColumn = $splitColumn;
    }

    /**
     * @return mixed
     */
    public function getSplitModel()
    {
        return $this->splitModel;
    }

    /**
     * @param mixed $splitModel
     */
    public function setSplitModel($splitModel): void
    {
        $this->splitModel = $splitModel;
    }


}

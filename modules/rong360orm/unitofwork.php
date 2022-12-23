<?php
class UnitOfWork
{
    private $entityAddList;
    private $entityModifyList;
    private $entityDeleteList;

    public function __construct(){
        $this->entityAddList = [];
        $this->entityModifyList = [];
        $this->entityDeleteList = [];
    }

    public function commit(){
        try{
            $this->startTranscation();
            $this->dispatchEntity();
            $this->saveEntity();
            $this->modifyEntity();
            $this->deleteEntity();
            return $this->commitDb();
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    private function startTranscation(){
    }

    private function dispatchEntity(){
        /**
         * @var EntityBase $entity
         */
        while($entity = LocalCache::getIns()->pop()){
            if(empty($entity->getSummary())){
                $this->entityAddList[] = $entity;
            } else if ($entity->getSummary() != $entity->calcSummary()) {
                $this->entityModifyList[] = $entity;
            } else if ($entity->isDeleted()) {
                $this->entityDeleteList[] = $entity;
            }
        }
    }

    private function saveEntity() {
        /**
         * @var EntityBase $entity
         */
        foreach($this->entityAddList as $entity) {
            DB::get("cuishou")->save($entity->getTable(), $entity->toArray());
        }
    }

    private function modifyEntity() {
        /**
         * @var EntityBase $entity
         */
        foreach($this->entityModifyList as $entity) {
            DB::get("cuishou")->update($entity->getTable(), $entity->toDbArray(), "id");
        }
    }

    private function deleteEntity() {
        /**
         * @var EntityBase $entity
         */
        foreach($this->entityDeleteList as $entity) {
            DB::get("cuishou")->delete($entity->getTable(), $entity->getId());
        }
    }

    private function rollback(){

    }

    private function commitDb(){}
}

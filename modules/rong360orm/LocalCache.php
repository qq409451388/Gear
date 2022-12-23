<?php
class LocalCache
{
    private static $ins;

    /**
     * @var array<EntityBase>
     */
    private $entityList = [];

    /**
     * @return LocalCache|null
     */
    public static function getIns() {
        if(null == self::$ins){
            self::$ins = new self();
        }
        return self::$ins;
    }

    private function generateKey($entityName, $id) {
        return $entityName.$id;
    }

    public function add(EntityBase $entity) {
        $key = $this->generateKey(get_class($entity), $entity->getId());
        $this->entityList[$key] = $entity;
    }

    public function getById($entityName, $id) {
        $key = $this->generateKey($entityName, $id);
        return $this->entityList[$key] ?? null;
    }

    public function pop() {
        return array_pop($this->entityList);
    }
}

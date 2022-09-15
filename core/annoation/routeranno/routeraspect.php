<?php
class RouterAspect extends Aspect
{
    public function check(): bool
    {
        if(!$this->getAtClass()->isSubclassOf(BaseController::class)){
            return false;
        }
        /**
         * 存在一个有效就往下走
         */
        $hasValid = false;
        foreach($this->getDependList() as $dependSon){
            if($dependSon->getAtMethod()->isPublic() && BaseController::class !== $dependSon->getAtMethod()->getDeclaringClass()->getName()){
                $hasValid = true;
            }
        }
        return $hasValid;
    }

    public function adhere(): void
    {
        foreach($this->getDependList() as $dependSon){
            $path = trim($this->getValue(), "/") . "/" .trim($dependSon->getValue(), "/");
            if(!empty($path)){
                EzRouter::get()->setMapping($path, $this->getAtClass()->getName(), $dependSon->getAtMethod()->getName());
            }
        }
    }
}
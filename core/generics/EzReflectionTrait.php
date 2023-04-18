<?php
trait EzReflectionTrait
{
    /**
     * @param Clazz<Anno> $annoClazz
     * @return AnnoItem|null
     * @throws Exception
     */
    public function getAnnoation(Clazz $annoClazz) {
        return AnnoationRule::searchAnnoation($this, $annoClazz);
    }
}

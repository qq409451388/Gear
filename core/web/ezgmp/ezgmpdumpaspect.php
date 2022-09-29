<?php
class EzGmpDumpAspect extends Aspect implements RunTimeAspect
{

    public function before(RunTimeProcessPoint $rpp): void
    {
        print_r(BeanFinder::get()->pull(EzLocalCache::class)->getAll());
    }

    public function after(RunTimeProcessPoint $rpp): void
    {
        print_r(BeanFinder::get()->pull(EzLocalCache::class)->getAll());
    }
}
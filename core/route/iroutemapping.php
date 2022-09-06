<?php
interface IRouteMapping
{
    public function disPatch(IRequest $request):string;
}
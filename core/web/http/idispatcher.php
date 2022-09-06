<?php
Interface IDispatcher
{
    public function judgePath(string $path):bool;
    public function matchedRouteMapping(string $path):IRouteMapping;
}
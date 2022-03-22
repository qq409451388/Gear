<?php
Interface IDispatcher
{
    public function judgePath(string $path):bool;
    public function dispatch(Request $request):Response;
}
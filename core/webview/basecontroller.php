<?php
class BaseController implements EzBean
{
    public function __get($obj){
        DBC::assertTrue(BeanFinder::get()->has($obj), '['.__CLASS__.'] class'.$obj.' is not exists!');
        return BeanFinder::get()->pull($obj);
    }

    protected function show($response, $path) {
        DBC::assertTrue(defined("TEMPLATE_DIR"), "[Controller] Must Define const TEMPLATE_DIR At Enter File!");
        extract($response);
        $template = strtolower(TEMPLATE_DIR.DIRECTORY_SEPARATOR.$path.'.php');
        ob_start();
        include($template);
        $res = ob_get_contents();
        ob_end_clean();
        return $this->response($res);
    }

    /**
     * @param $contentType HttpMimeType
     * @return IResponse
     */
    protected function response(string $content, $contentType = HttpMimeType::MIME_HTML):IResponse{
        return new Response(HttpStatus::OK(), $content, $contentType);
    }
}

<?php
class GetMapping implements Anno {
    public const ASPECT = RouterAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_METHOD;
    public const ISCOMBINATION = true;

    /**
     * @var string 路径
     */
    public $path;

    /**
     * @var array 路由参数匹配正则
     */
    public $argMatcher;

    public function combine($values)
    {
        $this->path = $values;
        return;
        preg_match_all("/(?<path>[\/a-zA-Z0-9]+)(?<args>[#{a-zA-z}]+)/", $values, $matched);
        $newPath = $values;
        $argMatcher = $values;
        foreach ($matched['args'] as $arg) {
            $newPath = str_replace($arg, EzRouter::URL_WILDCARD, $newPath);
        }

        foreach ($matched['args'] as $arg) {
            $arg = trim($arg, "{}");
            $argMatcher = str_replace($arg, "(?<$arg>[{a-zA-z0-9}]+)", $argMatcher);
        }
        $this->path = $newPath;
        $this->argMatcher = $argMatcher;
    }
}

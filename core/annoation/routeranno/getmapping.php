<?php
class GetMapping extends Anno implements AnnoationCombination {
    public const ASPECT = RouterAspect::class;
    public const POLICY = AnnoPolicyEnum::POLICY_BUILD;
    public const TARGET = AnnoElementType::TYPE_METHOD;
    public const STRUCT = AnnoValueTypeEnum::TYPE_NORMAL;

    /**
     * @var array 路由参数匹配正则
     */
    private $argMatcher;

    /**
     * @var string 路径
     */
    public function getPath() {
        return $this->value;
    }

    public function combine($values)
    {
        parent::combine($values);
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

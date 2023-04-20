<?php

class SysUtils
{
    public static function mem(bool $withUnUse = false)
    {
        return self::convert(memory_get_usage($withUnUse));
    }

    public static function memPeak(bool $withUnUse = false)
    {
        return self::convert(memory_get_peak_usage($withUnUse));
    }

    public static function convert(int $byte, int $precision = 2)
    {
        if ($byte < 1024) {
            return $byte . "byte";
        } elseif ($byte < 1048576) {
            return round($byte / 1024, $precision) . "KB";
        } elseif ($byte < 1073741824) {
            return round($byte / 1048576, $precision) . "MB";
        } else {
            return round($byte / 1073741824, $precision) . "GB";
        }
    }

    public static function scanDir($path, $deep = 1, $filterHidden = true)
    {
        $result = [];
        if ($deep == 0) {
            return $result;
        }
        $objs = @scandir($path);
        if (empty($objs)) {
            return $result;
        }
        foreach ($objs as $obj) {
            $tmpPath = $path . "/" . $obj;
            if (is_dir($tmpPath)) {
                if ("." == $obj || ".." == $obj) {
                    continue;
                }
                if ($filterHidden && self::judgeHiddenDir($obj)) {
                    continue;
                }
                $result[] = $tmpPath;
                $result = array_merge($result, self::scanDir($tmpPath, $deep - 1));
            }
        }
        return $result;
    }

    /**
     * 判断目录是否是隐藏目录
     * @param $dir
     * @return boolean
     * @throws GearUnsupportedOperationException|Exception
     */
    public static function judgeHiddenDir($dir): bool
    {
        if (Env::isWin()) {
            $filename = basename($dir);
            return substr($filename, 0, 1) === '.' || strpos($filename, '.\\') === 0;
        } else {
            if (Env::isUnix()) {
                $dir = dirname($dir);
                return substr($dir, 0, 1) === '.' || false !== strpos($dir, '/.');
            } else {
                DBC::throwEx(
                    "[SystemUtils] Unsupport OS for judgeHiddenDir!",
                    0,
                    GearUnsupportedOperationException::class
                );
            }
        }
    }

    public static function scanFile($path, $deep = 1, $filter = [], $holdFileKey = false)
    {
        $result = [];
        if ($deep == 0) {
            return $result;
        }
        $objs = @scandir($path);
        if (empty($objs)) {
            return $result;
        }
        foreach ($objs as $obj) {
            $tmpPath = $path . DIRECTORY_SEPARATOR . $obj;
            if (is_dir($tmpPath)) {
                if ("." == $obj || ".." == $obj) {
                    continue;
                }
                $result = array_merge($result, self::scanFile($tmpPath, $deep - 1, $filter, $holdFileKey));
            } else {
                if (is_file($tmpPath)) {
                    $pathInfo = pathinfo($tmpPath);
                    $fileExt = $pathInfo['extension'] ?? "";
                    if (empty($filter) || in_array($fileExt, $filter, true)) {
                        if ($holdFileKey) {
                            $fileName = $pathInfo['filename'] ?? "";
                            $result[$fileName] = $tmpPath;
                        } else {
                            $result[] = $tmpPath;
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function searchModules($dependencies) {
        $classes = [];
        foreach ($dependencies as $dependency) {
            foreach ($dependency as $d) {
                $path = GEAR_PATH.$d;
                $classes += self::scanFile($path, -1, ["php"], true);
            }
        }
        return $classes;
    }
}

<?php
class EzFileUtils
{
    public function eachProcessorLine($filePath, Closure $processor, $delimiter = "\t") {
        DBC::assertTrue(is_file($filePath), "file not exists: $filePath");
        $fileExtName = self::fileExtName($filePath);
        if (".csv" == $fileExtName) {
            $reader = "fgetcsv";
            $fomater = null;
        } else {
            $reader = "fgets";
            $fomater = function($line) use ($delimiter) {
                return explode($delimiter, trim($line));
            };
        }
        $fp = fopen($filePath, 'r');
        while ($line = $reader($fp)) {
            $line = $fomater($line);
            $processor($line);
        }
        fclose($fp);
    }

    public static function fileExtName($filePath) {
        DBC::assertTrue(is_file($filePath), "file not exists: $filePath");
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }
}
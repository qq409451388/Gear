<?php

class EzCurlBodyFile extends EzCurlBody
{
    /**
     * @var string 文件路径
     */
    private $filePath;

    /**
     * @var string 文件名
     */
    private $fileName;

    /**
     * @var string 文件拓展名
     */
    private $fileExt;

    /**
     * @var string 文件真实类型
     */
    private $fileRealType;

    /**
     * @var bool 是否已经分析过
     */
    private $isAnalysed = false;

    public function __construct($filePath = "") {
        parent::__construct();
        if (!empty($filePath)) {
            $this->setFilePath($filePath);
            $this->analyse();
        }
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     * @throws GearRunTimeException|Exception
     */
    public function setFilePath(string $filePath): void
    {
        DBC::assertTrue(is_file($filePath), "[EzCurl2] File Not Found from path:$filePath!");
        $this->filePath = $filePath;
        $this->setContentType();
    }

    protected function setContentType() {
        if (is_null($this->filePath)) {
            $this->contentType = "Content-Type: " . HttpMimeType::MIME_STREAM;
        } else {
            $this->contentType = "Content-Type: " . mime_content_type($this->filePath);
        }
    }

    /**
     * 返回文件的content
     * @return false|string
     * @throws GearRunTimeException|Exception
     */
    public function toString() {
        DBC::assertNotEmpty($this->filePath, "[EzCurl2] filePath:$this->filePath is not a file!");
        return file_get_contents($this->filePath);
    }

    /**
     * 分析文件，解析出拓展名、文件类型等信息
     */
    public function analyse() {
        if ($this->isAnalysed) {
            return;
        }
        $pathInfo = pathinfo($this->filePath);
        $this->fileName = $pathInfo['basename'] ?? "";
        $this->fileExt = $pathInfo['extension'] ?? "";
        $f = finfo_open(0);
        $finfo = finfo_file($f, $this->filePath);
        finfo_close($f);
        $this->fileRealType = $finfo;
        $this->isAnalysed = true;
    }

    public function isImage() {
        $this->analyse();
        return in_array(str_replace("Content-Type: ", "", $this->contentType), HttpMimeType::MIME_IMAGE_LIST);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }
}

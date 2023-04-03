<?php
class HttpMimeType
{
    public const EXT_AVI = "avi";
    public const MIME_AVI = "video/x-msvideo";

    public const EXT_MPEG = "mpeg";
    public const MIME_MPEG = "video/mpeg";

    public const EXT_WAV = "wav";
    public const MIME_WAV = "audio/x-wav";

    public const EXT_OGG = "ogg";
    public const MIME_OGG = "application/ogg";

    public const EXT_BMP = "bmp";
    public const MIME_BMP = "image/bmp";

    public const EXT_JPEG = "jpeg";
    public const EXT_JPG = "jpg";
    public const MIME_JPEG = "image/jpeg";

    public const EXT_GIF = "gif";
    public const MIME_GIF = "image/gif";

    public const EXT_PNG = "png";
    public const MIME_PNG = "image/png";

    public const EXT_AVIF = "avif";
    public const MIME_AVIF = "image/avif";

    public const EXT_WBMP = "wbmp";
    public const MIME_WBMP = "image/vnd.wap.wbmp";

    public const EXT_ICO = "ico";
    public const MIME_ICO = "image/x-icon";

    public const EXT_TEXT = "txt";
    public const EXT_HTML = "html";
    public const MIME_HTML = "text/html";
    public const MIME_PLAINTEXT = "text/plain";

    public const EXT_CSS = "css";
    public const MIME_CSS = "text/css";

    public const EXT_RTF = "rtf";
    public const MIME_RTF = "text/rtf";

    public const EXT_RTX = "rtx";
    public const MIME_RTX = "text/richtext";

    public const EXT_DOC = "doc";
    public const MIME_DOC = "application/msword";

    public const EXT_JS = "js";
    public const MIME_JS = "application/x-javascript'";

    public const EXT_SWF = "swf";
    public const MIME_SWF = "application/x-shockwave-flash";

    public const EXT_ZIP = "zip";
    public const MIME_ZIP = "application/zip";
    public const MIME_STREAM = "application/stream";

    public const MIME_TYPE_LIST = [
        self::EXT_AVI => self::MIME_AVI,
        self::EXT_MPEG => self::MIME_MPEG,
        self::EXT_WAV => self::MIME_WAV,
        self::EXT_OGG => self::MIME_OGG,
        self::EXT_BMP => self::MIME_BMP,
        self::EXT_JPEG => self::MIME_JPEG,
        self::EXT_JPG => self::MIME_JPEG,
        self::EXT_GIF => self::MIME_GIF,
        self::EXT_PNG => self::MIME_PNG,
        self::EXT_AVIF => self::MIME_AVIF,
        self::EXT_WBMP => self::MIME_WBMP,
        self::EXT_ICO => self::MIME_ICO,
        self::EXT_HTML => self::MIME_HTML,
        self::EXT_TEXT => self::MIME_PLAINTEXT,
        self::EXT_CSS => self::MIME_CSS,
        self::EXT_RTF => self::MIME_RTF,
        self::EXT_RTX => self::MIME_RTX,
        self::EXT_DOC => self::MIME_DOC,
        self::EXT_JS => self::MIME_JS,
        self::EXT_SWF => self::MIME_SWF,
        self::EXT_ZIP => self::MIME_ZIP
    ];

    /**
     * 图像类型的mime
     */
    public const MIME_IMAGE_LIST = [
        self::MIME_JPEG,
        self::MIME_BMP,
        self::MIME_PNG,
        self::MIME_GIF,
        self::MIME_AVIF,
        self::MIME_ICO,
        self::MIME_WBMP
    ];
}

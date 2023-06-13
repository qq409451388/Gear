<?php
class SchemaConst
{
    const HTTP = "http";
    const HTTPS = "https";
    const WEBSOCKET = "ws";
    const RESP = "resp";

    public static function isHttpOrSecurity() {
        return self::HTTP === Config::get("schema") || self::HTTPS === Config::get("schema");
    }

    public static function isWebSocket() {
        return self::WEBSOCKET === Config::get("schema");
    }
}
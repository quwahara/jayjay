<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Shorthands
 */
class SH
{
    public static function ss()
    {
        return Services::singleton();
    }

    public static function isJsonPost()
    {
        if (!array_key_exists('CONTENT_TYPE', $_SERVER)) return false;
        $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
        $media_type = $content_type[0];
        return $media_type == 'application/json' && $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public static function readJson()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public static function json($v)
    {
        return json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

}

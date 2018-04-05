<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

class URIService
{
  public static function toFullURI($filename)
  {
    return self::schemeAndHost() . self::replaceRequestURIWith($filename);
  }

  public static function replaceRequestURIWith($filename)
  {
    return dirname($_SERVER['REQUEST_URI']) . '/' . $filename;
  }

  public static function schemeAndHost()
  {
    return self::scheme() . '://' . $_SERVER['HTTP_HOST'];
  }

  public static function scheme()
  {
    return (isset($_SERVER['HTTPS']) ? "https" : "http");
  }

  public static function redirectByFilenameThenExit($filename)
  {
    header('Location: ' . self::toFullURI($filename));
    exit;
  }

}

<?php
namespace JJ;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;
use Services\CSSService;
use Services\DAObject;
use Services\DAService;
use Services\DBService;

class JJ
{
    public $config_;
    public $css_;
    public $dbdec_;
    public $da_;
    public $db_;

    public $mediaType;
    public $daos;
    public $models;
    public $methods;

    public function init($args)
    {
        $this->config_ = require __DIR__ . '/../config/global.php';
        $this->dbdec_ = require __DIR__ . '/../config/dbdec.php';
        $this->args = $args;
        // $this->initMediaType();
        $this->data = [
            'models' => [
                'status' => '',
            ],
            'io' => [
                'status' => '',
            ]
        ];
        if (array_key_exists('methods', $args)) {
            $this->methods = $args['methods'];
        }
        if (array_key_exists('models', $args)) {
            $this->initModels($args['models']);
        }
        // $this->initDAOs($args['models']);
        $this->dataJSON = $this->json($this->data);
        return $this;
    }

    // public function initMediaType()
    // {
    //     if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
    //         $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
    //     } else {
    //         $this->mediaType = '';
    //     }
    //     return $this;
    // }

    // public function initDAOs($models)
    // {
    //     $this->daos = [];
    //     foreach ($models as $model) {
    //         $this->daos[$model] = $this->dao($model);
    //         $this->data['models'][$model] = $this->daos[$model]->createModel();
    //     }
    //     return $this;
    // }

    public function initModels($models)
    {
        foreach ($models as $key => $value) {
            if (is_int($key) && is_string($value)) {
                if ($this->endsWith($value, '[]')) {
                    $model2 = mb_substr($value, 0, mb_strlen($value) - 2);
                    $theModel = [$this->dao($model2)->createModel()];
                } else {
                    $model2 = $value;
                    $theModel = $this->dao($model2)->createModel();
                }
            } else if (is_string($key) && is_array($value)) {
                if ($this->endsWith($key, '[]')) {
                    $model2 = mb_substr($key, 0, mb_strlen($key) - 2);
                    $theModel = [$value];
                } else {
                    $model2 = $key;
                    $theModel = $value;
                }
            } else {
                throw new Exception('The structure of models argument was bad.');
            }
            $this->data['models'][$model2] = $theModel;
        }
        return $this;
    }

    function css()
    {
        if (!$this->css_) {
            $cssc = $this->config_['css'];
            $this->css_ = (new CSSService())->init($cssc['baseFontSize']);
        }
        return $this->css_;
    }

    function db()
    {
        if (!$this->db_) {
            $dbc = $this->config_['db'];
            $this->db_ = (new DBService())
                ->init($dbc['dsn'], $dbc['username'], $dbc['password'], $dbc['options']);
        }
        return $this->db_;
    }

    function da()
    {
        if (!$this->da_) {
            $this->da_ = (new DAService())
                ->init($this->db()->pdo(), $this->dbdec_);
        }
        return $this->da_;
    }

    function beginTransaction(): bool
    {
        return $this->db()->pdo()->beginTransaction();
    }

    function commit(): bool
    {
        return $this->db()->pdo()->commit();
    }

    function rollBack(): bool
    {
        return $this->db()->pdo()->rollBack();
    }

    function dao($tableName)
    {
        return (new DAObject())->init($this->da(), $tableName);
    }

    public function isJsonPost()
    {
        if (!array_key_exists('CONTENT_TYPE', $_SERVER)) return false;
        $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
        $media_type = $content_type[0];
        return $media_type == 'application/json' && $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function readJson()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public function json($v)
    {
        return json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getMediaType()
    {
        if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
            $content_type = explode(';', trim($_SERVER['CONTENT_TYPE']));
            return $content_type[0];
        }
        return '';
    }

    public function dispatch()
    {
        $key = strtolower(trim($_SERVER['REQUEST_METHOD'] . ' ' . $this->getMediaType()));
        if (isset($this->args[$key])) {
            return $this->args[$key]($this);
        }
    }

    public function responseJson($code = 200)
    {
        http_response_code($code);
        header("Content-Type: application/json; charset=UTF-8");
        echo $this->json($this->data);
        exit();
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

}

return (function (array $args) {
    return (new JJ())->init($args)->dispatch();
});
?>
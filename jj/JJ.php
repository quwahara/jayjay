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

    public $daos;
    public $dispatchKey;
    public $loadJsonData;
    public $loadJsonDone;
    public $models;
    public $mediaType;
    public $methods;
    public $responseCode;
    public $xsrf;

    public function init($args)
    {
        $this->config_ = array_merge_recursive(
            require __DIR__ . '/../config/global-default.php',
            require __DIR__ . '/../config/global.php'
        );
        // $this->config_ = require __DIR__ . '/../config/global.php';
        $this->dbdec_ = require __DIR__ . '/../config/dbdec.php';
        $this->args = $args;
        $this->responseCode = 200;
        $this->loadJsonDone = false;
        // $this->initMediaType();

        session_start();
        if (empty($_SESSION['_xsrf'])) {
            $this->resetXsrf();
        }
        $this->initXsrf();
        setcookie('XSRF-TOKEN', $this->xsrf);

        $this->data = [
            'models' => [
                'status' => '',
                '_xsrf' => $this->xsrf,
            ],
            'io' => [
                'status' => '',
            ],
            '_dbg' => [
            ],
        ];
        if (array_key_exists('methods', $args)) {
            $this->methods = $args['methods'];
        }
        if (array_key_exists('models', $args)) {
            $this->initModels($args['models']);
        }
        if ($this->isJsonPost()) {
            $this->loadJson();
        }
        // $this->initDAOs($args['models']);
        $this->dataJSON = $this->json($this->data);


        if ($this->validateXsrf()) {
            $this->dispatchKey = strtolower(trim($_SERVER['REQUEST_METHOD'] . ' ' . $this->getMediaType()));
            $this->data['_dbg']['validatateXsrf_result'] = true; 
        } else {
            $this->responseCode = 400;
            $this->dispatchKey = 'error';
            $this->data['_dbg']['validatateXsrf_result'] = false; 
        }

        return $this;
    }

    /*

error: Error: Request failed with status code 403 at createError (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:913:16) at settle (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:883:13) at XMLHttpRequest.handleLoad (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:756:8)
config: {adapter: ƒ, transformRequest: {…}, transformResponse: {…}, timeout: 0, xsrfCookieName: "XSRF-TOKEN", …}
request: XMLHttpRequest {onreadystatechange: ƒ, readyState: 4, timeout: 0, withCredentials: false, upload: XMLHttpRequestUpload, …}
response:
config: {adapter: ƒ, transformRequest: {…}, transformResponse: {…}, timeout: 0, xsrfCookieName: "XSRF-TOKEN", …}
data: {models: {…}, io: {…}, _dbg: {…}}

headers:
cache-control: "no-store, no-cache, must-revalidate"
connection: "Keep-Alive"
content-length: "844"
content-type: "application/json; charset=UTF-8"
date: "Tue, 09 Oct 2018 15:26:15 GMT"
expires: "Thu, 19 Nov 1981 08:52:00 GMT"
keep-alive: "timeout=5, max=100"
pragma: "no-cache"
server: "Apache/2.4.33 (Unix) PHP/7.0.26"
x-powered-by: "PHP/7.0.26"
__proto__: Object

request: XMLHttpRequest {onreadystatechange: ƒ, readyState: 4, timeout: 0, withCredentials: false, upload: XMLHttpRequestUpload, …}
status: 403
statusText: "Forbidden"
__proto__: Object
message: "Request failed with status code 403"
stack: "Error: Request failed with status code 403↵    at createError (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:913:16)↵    at settle (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:883:13)↵    at XMLHttpRequest.handleLoad (http://localhost:8080/jayjay/public/js/lib/node_modules/axios/dist/axios.js:756:8)"

     */


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

    function initXsrf()
    {
        $this->xsrf = $_SESSION['_xsrf'];
        return $this;
    }

    function resetXsrf()
    {
        $_SESSION['_xsrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_xsrf'];
    }

    function xsrfHidden()
    {
        return "<input type='hidden' name='_xsrf' value='{$this->xsrf}'>";
    }

    function validateXsrf()
    {
        $headerName = 'HTTP_' . str_replace('-', '_', $this->config_['xsrf']['header_name']);
        if (isset($_SERVER[$headerName])) {
            $token = $_SERVER[$headerName];
            $this->data['_dbg']['vldxsrf'] = 'h'; 
        }
        // $token = $_SERVER["HTTP_{str_replace('-', '_', $this->config_['xsrf']['header_name'])}"];
        if (!isset($token)) {
            $token = $_COOKIE[$this->config_['xsrf']['cookie_name']];
            $this->data['_dbg']['vldxsrf'] = 'c'; 
        }
        if (!isset($token)) {
            $token = $_POST[$this->config_['xsrf']['hidden_name']];
            $this->data['_dbg']['vldxsrf'] = 'p'; 
        }
        return isset($this->xsrf) && $this->xsrf === $token;
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

    public function isJsonRequested()
    {
        return $this->getMediaType() == 'application/json';
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

    public function loadJson()
    {
        if ($this->loadJsonDone === false) {
            $this->loadJsonDone = true;
            $this->loadJsonData = json_decode(file_get_contents('php://input'), true);
            $this->data['io'] = $this->loadJsonData; 
            $this->data['_dbg']['loadJsonData'] = $this->loadJsonData; 
            $this->data['_dbg']['xxx'] = $_SERVER['HTTP_X_XSRF_TOKEN'];
        }
        return $this->loadJsonData;
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
        if (isset($this->args[$this->dispatchKey])) {
            return $this->args[$this->dispatchKey]($this);
        } else if ($this->dispatchKey === 'error') {
            return $this->dispatchError();
        }
    }

    public function dispatchError()
    {
        if ($this->isJsonRequested()) {
            $this->responseJson();
        } else {
            // TODO response error html in external file
            http_response_code($this->responseCode);
            echo 'error';
        }
    }

    public function responseJson()
    {
        http_response_code($this->responseCode);
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
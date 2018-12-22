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
    public $tables_;

    public $accessAllowed;
    public $daos;
    public $data;
    public $dispatchKey;
    public $loadJsonData;
    public $loadJsonDone;
    public $mediaType;
    public $methods;
    public $responseCode;
    public $structs;
    public $xsrf;

    public function initConfig(array $args)
    {
        $this->config_ = array_merge_recursive(
            require __DIR__ . '/../config/global-default.php',
            require __DIR__ . '/../config/global.php'
        );
        $this->dbdec_ = require __DIR__ . '/../config/dbdec.php';
        $this->tables_ = $this->dbdec_['tables'];
        $this->args = $args;
        return $this;
    }

    public function verifyAccess()
    {
        session_start();
        $this->initXsrf();

        if (!$this->isGet() || ($this->isGet() && $this->isLoggedIn())) {
            if (!$this->verifyXsrf()) {
                $this->logout();
                $this->responseForbiddenThenExit();
            }
        }

        setcookie($this->config_['xsrf']['cookie_name'], $this->xsrf);

        // Is this access had been logged-in in this session?
        if (!array_key_exists('access', $this->args) || $this->args['access'] !== 'public') {
            if (!array_key_exists($this->loggedInVarName(), $_SESSION) || !$_SESSION[$this->loggedInVarName()]) {
                $_SESSION['redirect_server_vars'] = $_SERVER;
                header('Location: ' . $this->config_['login']['redirect_path']);
                $this->accessAllowed = false;
                exit();
            }
        }

        $this->accessAllowed = true;

        return $this;
    }

    function initXsrf()
    {
        if (empty($_SESSION['_xsrf'])) {
            $_SESSION['_xsrf'] = bin2hex(random_bytes(32));
        }
        $this->xsrf = $_SESSION['_xsrf'];
        return $this;
    }

    function verifyXsrf()
    {
        $token = null;
        $headerName = 'HTTP_' . str_replace('-', '_', $this->config_['xsrf']['header_name']);
        if (isset($_SERVER[$headerName])) {
            $token = $_SERVER[$headerName];
        }
        if (!isset($token) && isset($_COOKIE[$this->config_['xsrf']['cookie_name']])) {
            $token = $_COOKIE[$this->config_['xsrf']['cookie_name']];
        }
        if (!isset($token) && isset($_POST[$this->config_['xsrf']['hidden_name']])) {
            $token = $_POST[$this->config_['xsrf']['hidden_name']];
        }
        $valid = isset($this->xsrf) && $this->xsrf === $token;
        return $valid;
    }

    /**
     * Response forbidden
     * 
     * This method exits and never returns.
     *
     * @return void
     */
    public function responseForbiddenThenExit()
    {
        $this->data = null;
        $this->responseCode = 403;  // Forbidden

        if ($this->isJsonRequested()) {
            $this->responseJsonThenExit();
        } else {
            $this->redirectThenExit($this->config_['access_denied']['redirect_path']);
        }
    }

    /**
     * Response internal server error
     * 
     * This method exits and never returns.
     *
     * @return void
     */
    public function responseInternalServerErrorThenExit()
    {
        $this->data = null;
        $this->responseCode = 500;  // Internal Server Error

        if ($this->isJsonRequested()) {
            $this->responseJsonThenExit();
        } else {
            $this->redirectThenExit($this->config_['internal_server_error']['redirect_path']);
        }
    }

    public function init()
    {
        $this->responseCode = 200;
        $this->loadJsonDone = false;

        $this->dispatchKey = strtolower(trim($_SERVER['REQUEST_METHOD'] . ' ' . $this->getMediaType()));

        $this->structs = $this->config_['structs'];

        $this->data = $this->config_['data'];

        if (array_key_exists('methods', $this->args)) {
            $this->methods = $this->args['methods'];
        }

        if (array_key_exists('structs', $this->args)) {
            $this->initStructs($this->args['structs']);
        }

        if ($this->isJsonPost()) {
            $this->loadJson();
        }

        return $this;
    }

    public function initStructs(array $structs) : JJ
    {
        foreach ($structs as $key => $value) {
            // The $value is name of struct if only $value is supplied and it is string
            // It creates struct from DAO by the name.
            if (is_int($key) && is_string($value)) {
                $isArray = $this->endsWith($value, '[]');
                if ($isArray) {
                    $struct2 = mb_substr($value, 0, mb_strlen($value) - 2);
                } else {
                    $struct2 = $value;
                }
                $dao = $this->dao($struct2);
                if ($isArray) {
                    $theStruct = [$dao->createStruct()];
                } else {
                    $theStruct = $dao->createStruct();
                }

            // The $key is name of struct if $key is string and $value is array.
            // The $value was assumed the struct itself.
            } else if (is_string($key) && is_array($value)) {

                if ($this->endsWith($key, '$')) {
                    continue;
                }
                if ($this->endsWith($key, '[]')) {
                    $struct2 = mb_substr($key, 0, mb_strlen($key) - 2);
                    $theStruct = [$value];
                } else {
                    $struct2 = $key;
                    $theStruct = $value;
                }
            } else {
                throw new Exception('The form of structs argument was bad.');
            }
            $this->structs[$struct2] = $theStruct;
        }
        return $this;
    }

    public function initAttrs(array $attrs) : JJ
    {
        foreach ($attrs as $attr) {
            $this->data['attrs'][$attr] = $this->dao($attr)->getAttrsAll();;
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

    function xsrfHidden()
    {
        return "<input type='hidden' name='_xsrf' value='{$this->xsrf}'>";
    }

    /**
     * Changes status login
     *
     * @param array     $extras Optional. Stores hash array into $_SESSION
     * @return array    Returns $extras. Returns an empty array if no $extras parameter was given . 
     */
    function login(array $extras = [])
    {
        $_SESSION[$this->loggedInVarName()] = true;
        unset($_SESSION['redirect_server_vars']);
        foreach ($extras as $k => $v) {
            $_SESSION[$k] = $v;
        }
        if (count($extras) > 0) {
            $_SESSION['_loggedin_extras'] = implode(',', array_keys($extras));
        }

        setcookie($this->config_['xsrf']['cookie_name'], $this->xsrf);

        return $extras;
    }

    /**
     * Changes status log out
     *
     * @return array    Returns $extras. Returns an empty array if no $extras parameter was given . 
     */
    function logout()
    {
        $extras = [];
        if (!$this->isLoggedIn()) {
            return $extras;
        }

        unset($_SESSION[$this->loggedInVarName()]);
        if (array_key_exists('_loggedin_extras', $_SESSION)) {
            foreach (explode(',', $_SESSION['_loggedin_extras']) as $k) {
                $extras[$k] = $_SESSION[$k];
                unset($_SESSION[$k]);
            }
            unset($_SESSION['_loggedin_extras']);
        }

        if (isset($_COOKIE[$this->config_['xsrf']['cookie_name']])) {
            unset($_COOKIE[$this->config_['xsrf']['cookie_name']]);
            setcookie($this->config_['xsrf']['cookie_name'], null, -1, '/');
        }

        return $extras;
    }

    function isLoggedIn()
    {
        if (array_key_exists($this->loggedInVarName(), $_SESSION)) {
            return $_SESSION[$this->loggedInVarName()];
        }
        return false;
    }

    function loggedInVarName() : string
    {
        return $this->config_['login']['loggedin_variable_name'];
    }

    function db()
    {
        if (!$this->db_) {
            $dbc = $this->config_['db'];
            $this->db_ = (new DBService())
                ->init($dbc['dsn'], $dbc['username'], $dbc['password'], $dbc['options'], $dbc['attributes']);
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

    function beginTransaction() : bool
    {
        return $this->db()->pdo()->beginTransaction();
    }

    function commit() : bool
    {
        return $this->db()->pdo()->commit();
    }

    function rollBack() : bool
    {
        return $this->db()->pdo()->rollBack();
    }

    function dao(string $tableName)
    {
        $table = $this->getTableByTableName($tableName);
        if (is_null($table)) {
            return null;
        }
        return (new DAObject())->init($this->db()->pdo(), $table);
        // return (new DAObject())->init($this->da(), $tableName);
    }

    public function getTableByTableName($tableName)
    {
        foreach ($this->dbdec_['tables'] as $table) {
            if ($table['tableName'] === $tableName) {
                return $table;
            } else if ($table['tableName.singular'] === $tableName) {
                return $table;
            }
        }
        return null;
    }

    public function isGet() : bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public function isPost() : bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    function dataAsJSON()
    {
        return $this->json($this->data);
    }

    function structsAsJSON()
    {
        return $this->json($this->structs);
    }

    public function isJsonRequested()
    {
        return $this->getMediaType() == 'application/json';
    }

    public function isJsonPost()
    {
        return $this->isPost() && $this->isJsonRequested();
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
            $this->data = $this->loadJsonData;
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
            return isset($content_type[0]) ? $content_type[0] : '';
        }
        return '';
    }

    public function dispatch()
    {
        if (isset($this->args[$this->dispatchKey])) {
            $this->args[$this->dispatchKey]($this);
        }

        if ($this->isJsonRequested()) {
            $this->responseJsonThenExit();
        }
    }

    public function redirectThenExit(string $location)
    {
        http_response_code($this->responseCode);
        header('Location: ' . $location);
        exit();
    }

    public function responseJsonThenExit()
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
    $jj = (new JJ())->initConfig($args);
    try {
        $jj->verifyAccess();
        if ($jj->accessAllowed) {
            $jj->init()->dispatch();
        }
    } catch (\Throwable $th) {
        throw $th;
        // $jj->responseInternalServerErrorThenExit();
        error_log(var_export($th, true));
    }
    return $jj;
});
?>
<?php
namespace JJ;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;
use Services\DAObject;
use Services\DAService;
use Services\DBService;
use Services\PartService;

function bool_in_ini($var)
{
    if (strtolower(strval($var)) === 'off') {
        return false;
    }
    return boolval($var);
}

/**
 * The structs and attrs are separated.
 * The reason for separation is that property of same name is used in different aspect.
 * In case of structs binding, property is used as primitive, then bind to one elment or one attribute.
 * In case of attrs binding, property is used as object, then bind to many attibutes at once.
 * The structs and attrs are separated to saticfy both cases.
 */
class JJ
{
    public $config_;
    public $dbdec_;
    public $da_;
    public $db_;
    public $part_;
    public $required_;
    public $tables_;

    public $accessAllowed;

    /**
     * Starting up parameters
     *
     * @var array
     */
    public $args;
    public $daos;
    public $data;
    public $dispatchKey;
    public $downloadJsonData;
    public $downloadJsonFilename;
    public $loadJsonData;
    public $loadJsonDone;
    public $mediaType;
    public $methods;
    public $responseCode;
    public $structs;
    public $xsrf;

    public function initConfig(array $args)
    {
        $this->required_ = [];
        $this->config_ = array_replace_recursive(
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
     * This method exits but never returns.
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
     * This method exits but never returns.
     *
     * @return void
     */
    public function responseInternalServerErrorThenExit()
    {
        if (!bool_in_ini(ini_get('display_errors'))) {
            $this->data = null;
        }
        $this->responseCode = 500;  // Internal Server Error

        if ($this->isJsonRequested()) {
            $this->responseJsonThenExit();
        } else {
            $this->redirectThenExit($this->config_['internal_server_error']['redirect_path']);
        }
    }

    public function init()
    {
        $this->downloadJsonData = null;
        $this->downloadJsonFilename = null;
        $this->loadJsonDone = false;
        $this->responseCode = 200;

        $this->dispatchKey = strtolower(trim($_SERVER['REQUEST_METHOD'] . ' ' . $this->getMediaType()));


        $this->structs = [];
        if (array_key_exists('structs', $this->config_)) {
            $this->initStructs($this->config_['structs']);
        }

        $this->attrs = [];
        if (array_key_exists('attrs', $this->config_)) {
            $this->initAttrs($this->config_['attrs']);
        }

        $this->data = [];
        if (array_key_exists('data', $this->config_)) {
            $this->data = $this->config_['data'];
        }

        $args = &$this->args;
        if (array_key_exists('init', $args)) {
            call_user_func($args['init']->bindTo($this));
        }

        if (array_key_exists('structs', $args)) {
            $this->initStructs($args['structs']);
        }

        if (empty($this->structs)) {
            // prevent to be array for json_encode
            $this->structs['___'] = '';
        }

        if (array_key_exists('attrs', $args)) {
            $this->initAttrs($args['attrs']);
        }

        if (empty($this->attrs)) {
            // prevent to be array for json_encode
            $this->attrs['___'] = '';
        }

        if ($this->isGet()) {
            $this->data = array_merge($this->data, $this->structs);
        }

        if ($this->isJsonPost()) {
            $this->loadJson();
        }

        return $this;
    }

    public function __call($name, $args)
    {
        if (!array_key_exists($name, $this->args)) {
            throw new \RuntimeException("Method {$name} does not exist");
        }
        $callable = $this->args[$name];
        if (!is_callable($callable)) {
            throw new \RuntimeException("Method {$name} was not callable");
        }
        return call_user_func_array($callable->bindTo($this), $args);
    }

    /**
     * Initialize $structs argument
     *
     * @param array $structs definition of structs
     * @return JJ this instance
     */
    public function initStructs(array $structs): JJ
    {
        $theStructs = [];
        foreach ($structs as $key => $value) {
            $substruct = $this->parseStruct($key, $value);
            $theStructs = $theStructs + $substruct;
        }
        $this->structs = array_merge_recursive($this->structs, $theStructs);
        return $this;
    }

    /**
     * Parse key and value to struct
     *
     * Case 1, $key is int and $value is string
     * In this case, the $value is assumed table named and it loads strunct by table name.
     * 
     * Case 2, $key is string and $value is array
     * It accumrates each items in array that were divided from $value. 
     * 
     * Case 3, $key is string and $value is primitive value, like string, int and bool.
     * In this case, $key and $value is struct itself.
     * 
     * @param [type] $key
     * @param [type] $value
     * @return array derived struct
     */
    public function parseStruct($key, $value): array
    {
        if (is_int($key) && is_string($value)) {
            return $this->loadStructByTableName($value);
        } else if (is_string($key) && is_array($value)) {

            $isArray = $this->endsWith($key, '[]');
            if ($isArray) {
                $key2 = mb_substr($key, 0, mb_strlen($key) - 2);
            } else {
                $key2 = $key;
            }

            // merge everything
            $theStruct = [];
            foreach ($value as $key3 => $value3) {
                // In case of $value3 is model name
                if (is_int($key3) && is_string($value3)) {
                    // Using only structure of model. Rips $key4 has model name.
                    $substruct = $this->loadStructByTableName($value3);
                    foreach ($substruct as $key4 => $value4) {
                        $theStruct = $theStruct + $value4;
                    }
                } else {
                    $substruct = $this->parseStruct($key3, $value3);
                    $theStruct = $theStruct + $substruct;
                }
            }
            if ($isArray) {
                return [$key2 => [$theStruct]];
            } else {
                return [$key2 => $theStruct];
            }
        } else if (is_string($key) && (is_string($value) || is_numeric($value) || is_bool($value))) {
            // create a key-value pair
            // This is going to be merged after this function returns.
            if ($this->endsWith($key, '[]')) {
                $key2 = mb_substr($key, 0, mb_strlen($key) - 2);
                return [$key2 => [$value]];
            } else {
                return [$key => $value];
            }
        } else {
            throw new Exception('The form of structs argument was bad.');
        }
    }

    /**
     * Load struct by Table name
     *
     * @param [string] $name Table name to be loaded. This load struct as array if $name is ending with "[]".
     * @return array loaded struct
     */
    public function loadStructByTableName($name): array
    {
        $theStruct = [];
        $isArray = $this->endsWith($name, '[]');
        if ($isArray) {
            $name2 = mb_substr($name, 0, mb_strlen($name) - 2);
        } else {
            $name2 = $name;
        }
        $dao = $this->dao($name2);
        if ($isArray) {
            return [$name2 => [$dao->createStruct()]];
        } else {
            return [$name2 => $dao->createStruct()];
        }
    }

    /**
     * Initialize $attr argument
     *
     * @param array $attrs definition of attrs
     * @return JJ this instance
     */
    public function initAttrs(array $attrs): JJ
    {
        $theAttrs = [];
        foreach ($attrs as $key => $value) {
            $subattr = $this->parseAttr($key, $value);
            // $theAttrs = $theAttrs + $subattr;
            $theAttrs = $subattr + $theAttrs;
        }
        $this->attrs = array_merge($this->attrs, $theAttrs);
        return $this;
    }

    /**
     * Parse key and value to attr
     *
     * Case 1, $key is int and $value is string
     * In this case, the $value is assumed table named and it loads attr by table name.
     * 
     * Case 2, $key is string and $value is array
     * It accumrates each items in array that were divided from $value. 
     * 
     * Case 3, $key is string and $value is primitive value, like string, int and bool.
     * In this case, $key and $value is attr itself.
     * 
     * @param [type] $key
     * @param [type] $value
     * @return array derived attr
     */
    public function parseAttr($key, $value): array
    {
        if (is_int($key) && is_string($value)) {
            return $this->dao((string)$value)->getAttrsAll();
        } else if (is_string($key) && is_array($value)) {

            // merge everything
            $theAttr = [];
            foreach ($value as $key3 => $value3) {
                // In case of $value3 is model name
                if (is_int($key3) && is_string($value3)) {
                    // Using only structure of model. Rips $key4 that has model name.
                    $subattr = $this->dao($value3)->getAttrsAll();
                    if (is_array($subattr)) {
                        foreach ($subattr as $key4 => $value4) {
                            $theAttr[$key4] =  $value4;
                        }
                    }
                } else {
                    $subattr = $this->parseAttr($key3, $value3);
                    $theAttr = $subattr + $theAttr;
                }
            }
            return [$key => $theAttr];
        } else if (is_string($key) && (is_string($value) || is_numeric($value) || is_bool($value))) {
            // create a key-value pair
            // This is going to be merged after this function returns.
            return [$key => $value];
        } else {
            throw new Exception('The form of attrs argument was bad.');
        }
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

    function loggedInVarName(): string
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

    function beginTransaction(): void
    {
        if (!$this->db()->pdo()->beginTransaction()) {
            throw new Exception('PDO::beginTransaction failed');
        }
    }

    function commit()
    {
        if (!$this->db()->pdo()->commit()) {
            throw new Exception('PDO::commit failed');
        }
    }

    function rollBack(): bool
    {
        return $this->db()->pdo()->rollBack();
    }

    function dao(string $tableName, array $subtableNames = [])
    {
        $table = $this->getTableByTableName($tableName);
        if (is_null($table)) {
            return null;
        }
        $subtables = [];
        foreach ($subtableNames as $subtableName) {
            $subtable = $this->getTableByTableName($subtableName);
            if (is_null($subtable)) {
                return null;
            }
            $subtables[] = $subtable;
        }
        return (new DAObject())->init($this->db()->pdo(), $table, $subtables)->setSqlTrace($this->config_['debug']['enabled']);
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

    function part()
    {
        if (!$this->part_) {
            $this->part_ = (new PartService())
                ->init($this->dao('part'), $this->dao('part_properties'), $this->dao('part_items'));
        }
        return $this->part_;
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public function isPost(): bool
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

    function attrsAsJSON()
    {
        return $this->json($this->attrs);
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

    public function hasId()
    {
        return array_key_exists('id', $_REQUEST);
    }

    public function getId()
    {
        if ($this->hasId()) {
            return $_REQUEST['id'];
        } else {
            return null;
        }
    }

    public function getRequest($key, $val = null)
    {
        if (array_key_exists($key, $_REQUEST)) {
            return $_REQUEST[$key];
        } else {
            return $val;
        }
    }

    public function getRequestAsInt($key, $val = null)
    {
        if (array_key_exists($key, $_REQUEST)) {
            return intVal($_REQUEST[$key]);
        } else {
            return intVal($val);
        }
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
            try {
                $this->beginTransaction();
                ($this->args[$this->dispatchKey])->bindTo($this, $this)();
                $this->commit();
            } catch (\Throwable $th) {
                try {
                    $rb = $this->rollBack();
                    $rbth = null;
                } catch (\Throwable $th2) {
                    $rb = false;
                    $rbth = $th2;
                }
                $lines = explode("\n", (string)$th);
                foreach ($lines as $line) {
                    error_log($line);
                }
                $rblines = [];
                if (!$rb) {
                    error_log('PDO::rollBack failed');
                    if ($rbth) {
                        $rblines = explode("\n", (string)$rbth);
                        foreach ($rblines as $rbline) {
                            error_log($rbline);
                        }
                    }
                }

                $this->data = ['debug' => ['lines' => $lines + $rblines]];
                $this->responseInternalServerErrorThenExit();
            }
        }

        if ($this->isJsonRequested()) {
            $this->responseJsonThenExit();
        }

        if (!is_null($this->downloadJsonFilename)) {
            $this->downloadJsonThenExit();
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

    public function downloadJsonThenExit()
    {
        http_response_code($this->responseCode);
        header("Content-Type: application/json; charset=UTF-8");
        header("Content-disposition: attachment; filename={$this->downloadJsonFilename}");
        echo $this->json($this->downloadJsonData);

        exit();
    }

    function validate($name, $type, $value, $conditions): array
    {
        $violations = [];
        if ($type === 'string') {
            $isEmpty = empty($value);
            if (array_key_exists('required', $conditions)) {
                if ($isEmpty) {
                    $violations[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $value,
                        'violation' => 'required'
                    ];
                }
            }
            if (!$isEmpty && !is_string($value)) {
                throw new \RuntimeException("Type of \$value was not string");
            }
            $s = strval($value);
            $len = mb_strlen($s);
            if (array_key_exists('minlength', $conditions)) {
                if ($len < $conditions['minlength']) {
                    $violations[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $value,
                        'violation' => 'minlength',
                        'minlength' => $conditions['minlength']
                    ];
                }
            }
            if (array_key_exists('maxlength', $conditions)) {
                if ($len > $conditions['maxlength']) {
                    $violations[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $value,
                        'violation' => 'maxlength',
                        'maxlength' => $conditions['maxlength']
                    ];
                }
            }
            return $violations;
        } else if ($type === 'number') {
            $isNull = is_null($value);
            if (array_key_exists('required', $conditions)) {
                if ($isNull) {
                    $violations[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $value,
                        'violation' => 'required'
                    ];
                }
            }
            if (!$isNull && !is_numeric($value)) {
                throw new \RuntimeException("Type of \$value was not string");
            }
            if (!$isNull) {
                $n = floatval($value);
                if (array_key_exists('min', $conditions)) {
                    if ($n < $conditions['min']) {
                        $violations[] = [
                            'name' => $name,
                            'type' => $type,
                            'value' => $value,
                            'violation' => 'min',
                            'min' => $conditions['min']
                        ];
                    }
                }
                if (array_key_exists('max', $conditions)) {
                    if ($n > $conditions['max']) {
                        $violations[] = [
                            'name' => $name,
                            'type' => $type,
                            'value' => $value,
                            'violation' => 'max',
                            'max' => $conditions['max']
                        ];
                    }
                }
            }
            return $violations;
        } else {
            throw new \RuntimeException("Unsupported type: {$type} for validation");
        }
    }


    function requireBy2($name)
    {
        if (array_key_exists($name, $this->required_)) {
            return $this->required_[$name];
        }
        if (array_key_exists($name, $this->config_['requires'])) {
            $required = require __DIR__ . '/../' . $this->config_['requires'][$name];
            $this->required_[$name] = $required;
            return $required;
        }
        return null;
    }

    function initStructsBy($name)
    {
        $required = $this->requireBy2($name);
        if (!is_null($required)) {
            $this->initStructs($required['structs']);
        }
        return $this;
    }

    function echoBy($name)
    {
        $required = $this->requireBy2($name, $args = []);
        if (!is_null($required)) {
            return call_user_func_array($required['echo']->bindTo($this), $args);
        }
    }

    function requireBy($name)
    {
        if (array_key_exists($name, $this->config_['requires'])) {
            require __DIR__ . '/../' . $this->config_['requires'][$name];
        }
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
    ob_start();

    $jj = (new JJ())->initConfig($args);
    try {
        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $jj->verifyAccess();
        if ($jj->accessAllowed) {
            $jj->init()->dispatch();
        }
    } catch (\Throwable $th) {
        $lines = explode("\n", (string)$th);
        foreach ($lines as $line) {
            error_log($line);
        }
        // throw $th;
        $jj->responseInternalServerErrorThenExit();
        // error_log(var_export($th, true));
    }

    @ob_end_flush();

    return $jj;
});

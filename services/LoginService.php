<?php
namespace Services;

require_once 'vendor/autoload.php';

class LoginService
{
  const NO_USER = 'no user';
  const PASSWORD_UNMATCH = 'password unmatch';
  const AUTHORIZED = 'authorized';

  public $db;

  public function init($db)
  {
    $this->db = $db;
    return $this;
  }

  public function authorize($username, $password)
  {
    $userOpe = $this->db->loadOperation('Entities\Users');
    $user = $userOpe->newInstance();
    $userOpe->setPropertiesFrom($user, $_POST);

    $found = $userOpe->findOneByPrimaryKey($username);

    if (!$found) {
      return self::NO_USER;
    } else {
      if ($found['password'] !== $password) {
        return self::PASSWORD_UNMATCH;
      } else {
        return self::AUTHORIZED;
      }
    }
  }

}

<?php
class SessionStorage
{
    public function __construct($cookieName = 'PHP_SESS_ID')
    {
        session_name($cookieName);
        session_start();
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key];
    }
}

class User
{
	//以类成员变量方式注入
    public $sessionStorage;

    public function setLanguage($language)
    {
        $this->sessionStorage->set('language', $language);
    }

    public function getLanguage()
    {
        return $this->sessionStorage->get('language');
    }
}

$storage = new SessionStorage('SESSION_ID');
$user = new User();
$user->sessionStorage = $storage;
$user->setLanguage('fr');
$user_language = $user->getLanguage();
echo $user_language;
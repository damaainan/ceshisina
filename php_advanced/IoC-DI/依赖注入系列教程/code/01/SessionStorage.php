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
    protected $storage;

    public function __construct()
    {
        $this->storage = new SessionStorage();
    }

    public function setLanguage($language)
    {
        $this->storage->set('language', $language);
    }

    public function getLanguage()
    {
        return $this->storage->get('language');
    }
}

$user = new User();
$user->setLanguage('fr');
$user_language = $user->getLanguage();
echo $user_language;
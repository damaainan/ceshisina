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
	//以构造函数注入
    public function __construct($storage)
    {
        $this->storage = $storage;
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

$storage = new SessionStorage('SESSION_ID');
$user = new User($storage);

$user->setLanguage('fr');
$user_language = $user->getLanguage();
echo $user_language;
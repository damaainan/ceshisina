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
	//以设值方法注入
    public function setSessionStorage($storage)
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
$user = new User();
$user->setSessionStorage($storage);
$user->setLanguage('fr');
$user_language = $user->getLanguage();
echo $user_language;
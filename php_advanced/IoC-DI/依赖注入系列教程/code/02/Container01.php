<?php
class Container
{
    public function getMailTransport()
    {
        return new Zend_Mail_Transport_Stmp('stmp.gmail.com', array(
            'auth'     => 'login',
            'username' => 'foo',
            'password' => 'bar',
            'ssl'      => 'ssl',
            'port'     => 465,
        ));
    }

    public function getMailer()
    {
        $mailer = new Zend_Mail();
        $mailer->setDefaultTransport($this->getMailTransport());

        return $mailer;
    }
}

$container = new Container();
$mailer = $container->getMailer();
<?php
class Container
{
    protected $parameters = array();

    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    public function getMailTransport()
    {
        return new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
        'auth'     => 'login',
        'username' => $this->parameters['mailer.username'],
        'password' => $this->parameters['mailer.password'],
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

$container = new Container(array(
        'mailer.username' => 'foo',
        'mailer.password' => 'bar',
    ));

$mailer = $container->getMailer()
<?php

namespace DependencyInjection;

/**
 * Connection类
 */
class Connection
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Currently connected host
     */
    protected $host;

    /**
     * @param Parameters $config
     */
    public function __construct(Parameters $config)
    {
        $this->configuration = $config;
    }

    /**
     * connection using the injected config
     */
    public function connect()
    {
        $host = $this->configuration->get('host');
        // connection to host, authentication etc...

        //if connected
        $this->host = $host;
    }

    /*
     * 获取当前连接的主机
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
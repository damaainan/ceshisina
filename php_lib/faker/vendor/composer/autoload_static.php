<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite7dd9d5c6f5232f54998390627624381
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Faker\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Faker\\' => 
        array (
            0 => __DIR__ . '/..' . '/fzaninotto/faker/src/Faker',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite7dd9d5c6f5232f54998390627624381::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite7dd9d5c6f5232f54998390627624381::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

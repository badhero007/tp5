<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit547f1ba8138a17d6b867624279d0c7eb
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
            'think\\' => 6,
        ),
        'P' => 
        array (
            'Predis\\' => 7,
            'PhpAmqpLib\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'think\\' => 
        array (
            0 => __DIR__ . '/../..' . '/thinkphp/library/think',
        ),
        'Predis\\' => 
        array (
            0 => __DIR__ . '/..' . '/predis/predis/src',
        ),
        'PhpAmqpLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-amqplib/php-amqplib/PhpAmqpLib',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit547f1ba8138a17d6b867624279d0c7eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit547f1ba8138a17d6b867624279d0c7eb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit547f1ba8138a17d6b867624279d0c7eb::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}

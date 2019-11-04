<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb669054afbbf04f8259f75018207e62
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Vendidero\\Germanized\\Shipments\\' => 31,
        ),
        'A' => 
        array (
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Vendidero\\Germanized\\Shipments\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Automattic\\Jetpack\\Autoloader\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteb669054afbbf04f8259f75018207e62::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteb669054afbbf04f8259f75018207e62::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

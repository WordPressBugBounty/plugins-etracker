<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd36b552c7d2269145c8f5647880437dc
{
    public static $files = array (
        '97be8d00d4e1b8596dda683609f3dce2' => __DIR__ . '/..' . '/tcdent/php-restclient/restclient.php',
    );

    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Etracker\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Etracker\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd36b552c7d2269145c8f5647880437dc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd36b552c7d2269145c8f5647880437dc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd36b552c7d2269145c8f5647880437dc::$classMap;

        }, null, ClassLoader::class);
    }
}
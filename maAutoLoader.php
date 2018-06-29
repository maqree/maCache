<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * 自动加载类
 */
namespace macache;
class maAutoLoader{
    /**
     * 工作目录
     */
    static $cwd = __DIR__;

    /**
     * 根命名空间
     */
    static $rootSpace = "macache";

    /**
     * 自动加载函数
     */
    static function loader($className, $suffix = "php"){
        $className = preg_replace("/\\\/", DIRECTORY_SEPARATOR, $className);
        $classIndex = explode(DIRECTORY_SEPARATOR, $className);
        $fileName = array_pop($classIndex) . "." . $suffix;
        array_shift($classIndex);
        $path = !empty($classIndex) ? implode(DIRECTORY_SEPARATOR, $classIndex) : "";
        $dir = static::$cwd  . DIRECTORY_SEPARATOR . ($path ? ($path . DIRECTORY_SEPARATOR) : "");
        $filePath = $dir . $fileName;
        if(file_exists($filePath)){
            @include($filePath);
        }
    }
}
spl_autoload_register("macache\\maAutoLoader::loader");
class_alias('macache\\maCache','maCache');
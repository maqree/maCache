<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class controller
 * 缓存控制器
 */
namespace macache\src;
use macache\src\cacheFileOp;
use macache\src\inf\controller;

class cacheController implements controller {

    /**
     * @var
     * 缓存文件名
     */
    static $cacheFile;
    /**
     * @var
     * 缓存文件操作实例
     */
    static $cacheFileObj;

    /**
     * 构造函数
     */
    public function __construct($cacheFileName){
        static::$cacheFile = $cacheFileName;
    }

    /**
     * 存数据
     * $key 键值
     * $data 数据
     * $expire 过期时间，整数秒，-1表示永不过期
     */
    public function set($key, $data, $expire = -1){
        $this->__createFileOp("set");
        return static::$cacheFileObj->write($this->__getKey($key), $data, $expire);
    }

    /**
     * 取数据
     * $key 键值
     */
    public function get($key){
        $this->__createFileOp("get");
        return static::$cacheFileObj->read($this->__getKey($key));
    }

    /**
     * 过滤KEY值
     * $key 键值
     */
    private function __getKey($key){
        return trim($key);
    }

    /**
     * 创建操作实例
     * $scene set||get
     */
    private function __createFileOp($scene){
        static::$cacheFileObj = new cacheFileOp(static::$cacheFile, $scene);
    }

}

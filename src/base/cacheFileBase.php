<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class cacheFileBase
 * 缓存文件的基类，索引类和数据类都继承它
 */
namespace macache\src\base;
use macache\maCache;
use macache\src\file;

class cacheFileBase {
    /**
     * 文件头
     */
    static $head = "<?php exit();?>";
    /**
     * 头部字符串的长度共15个字节
     */
    static $headLenth = 15;

    /**
     * 索引后缀
     */
    static $suffix = "";

    /**
     * @var
     * 文件的绝对路径
     */
    static $filePath;


    /**
     * 构造函数
     * $cacheFileName 缓存文件名
     * $scene 场景 set||get
     */
    public function __construct($cacheFileName, $scene){
        static::$filePath = "";
        $filePath = maCache::getCacheDir() . DIRECTORY_SEPARATOR . sprintf(static::$suffix, $cacheFileName);
        $fileExists = file_exists($filePath);
        $fileExists && (static::$filePath = $filePath);
        // 写缓存的时候才新增文件
        if(!static::$filePath && $scene == "set" ){
            $isFirst = false;
            if(!file_exists($filePath)){
                $isFirst = true;
                $handle = fopen($filePath, "w");
                fclose($handle);
            }
            static::$filePath = $filePath;
            $isFirst && static::initData();
        }
    }

    /**
     * 初始化数据
     */
    static function initData(){
        $content = static::$head;
        file::fileWrite(static::$filePath, $content);
    }

    /**
     * 索引长度, 满存文件800M，1700个缓存
     * 128字节1000个     100M
     * 256的  500个      100M
     * 1M的   100个      100M
     * 5M的   100个      500M
     */
    static function indexIndex(){
        return array(
            128  => 1000,
            256  => 500,
            1000 => 100,
            5000 => 100
        );
    }

    /**
     * 索引节点
     * K  = KEY             键值
     * P  = POSITON         开始的位置
     * L  = LENGTH          字符的实际长度
     * VL = VIRLENGTH       字符的填充长度
     * C  = COUNT           命中次数
     * E  = EXPIRE          过期时间
     * CT = CREATE          创建时间
     */
    static function indexNote(){
        return array("K" => "", "P" => 0, "L" => 0, "VL" => 0, "C" => 0, "E" => -1, "CT" => "");
    }
}
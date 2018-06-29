<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class cacheFileData
 * 缓存数据相关
 */
namespace macache\src;
use macache\src\base\cacheFileBase;
use macache\src\file;
class cacheFileData extends cacheFileBase{
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
    static $suffix = "%s.php";

    /**
     * @var
     * 缓存文件的绝对路径
     */
    static $filePath;

    /**
     * 构造函数
     * $cacheFileName 缓存文件名
     * $scene 场景 set||get
     */
    public function __construct($cacheFileName, $scene){
        parent::__construct($cacheFileName, $scene);
    }

    /**
     * 获取文件的物理路径
     */
    public function getFilePath(){
        return static::$filePath;
    }

    /**
     * 写数据
     * $pos 在缓存文件中定位的起始位置
     * $realData 数据内容，通常是序列化后的数据
     */
    public function writeData($pos, $realData){
        file::fileWrite(static::$filePath, $realData, $pos);
    }

    /**
     * 取数据
     * $node 从索引文章中找到的节点
     */
    public function readData($node){
        $data = file::fileRead(static::$filePath, $node["L"], $node["P"]);
        return unserialize($data);
    }
}
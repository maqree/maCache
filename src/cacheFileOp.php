<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class cacheFileOp
 * 缓存数据操作
 */

namespace macache\src;
use macache\src\cacheFileData;
use macache\src\cacheFileIndex;
use macache\src\file;

class cacheFileOp {

    /**
     * @var
     * 索引句柄
     */
    static $index;

    /**
     * @var
     * 缓存数据实例
     */
    static $data;

    /**
     * 当前的缓存文件名
     */
    static $cacheFileName;


    /**
     * 构造函数
     * $cacheFileName 缓存文件名
     * $scene 场景 set||get
     */
    public function __construct($cacheFileName, $scene = "get"){
        static::$cacheFileName = $cacheFileName;
        static::$index = new cacheFileIndex($cacheFileName, $scene);
        static::$data  = new cacheFileData($cacheFileName, $scene);
    }

    /**
     * 读数据
     * $key 键值
     */
    public function read($key){
        if(!static::$index->getFilePath()) return "";
        $node = static::$index->getReadNode($key);
        if(!$node) return "";
        return static::$data->readData($node);
    }

    /**
     * 写数据
     * $key 键值
     * $data 数据
     * $expire 过期时间，整数秒，-1表示永不过期
     */
    public function write($key, $data, $expire = -1){
        $realData = serialize($data);
        $dataLength = strlen($realData);
        $posData = $this->__updateIndex($key, $dataLength, $expire);
        $virLength = $posData["virLength"];
        $pos = $posData["pos"];
        $diff = ($virLength - $dataLength);
        $virStr = str_repeat(pack("C", "32"), $diff);
        $realData .= $virStr;
        static::$data->writeData($pos, $realData);

    }

    /**
     * 更新节点, 并返回缓存要写入的位置和虚拟长度
     * $key 键值
     * $dataLength 序列化后的长度
     * $expire  过期时间，整数秒
     */
    private function __updateIndex($key, $dataLength, $expire){
        static::$index->getWriteNode($key, $dataLength, $nodeData);
        $node = &$nodeData["index"][$nodeData["indexK"]][$nodeData["nodeK"]];
        $node["K"]     = $key;
        $node["VL"]    = $nodeData["indexK"];
        $node["L"]     = $dataLength;
        $node["E"]     = intval($expire);
        $node["CT"]    = time();
        // 如果是空节点
        if(isset($node["P"]) && empty($node["P"])){
            // 获取缓存文件的屁股位置
            $node["P"] = file::getWriteSeek(static::$data->getFilePath());
        }
        // 更新缓存
        static::$index->upIndex($nodeData["index"]);
        // 返回缓存文件的位置
        return array("pos" => $node["P"], "virLength" => $nodeData["indexK"]);
    }

}
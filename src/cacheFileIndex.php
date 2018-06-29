<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class cacheFileIndex
 * 缓存索引相关
 */
namespace macache\src;
use macache\src\file;
use macache\src\base\cacheFileBase;

class cacheFileIndex extends cacheFileBase{
    /**
     * 文件头
     */
    static $head = "<?php exit();?>";
    /**
     * 头部字符串的长度共15个字节 + 4个字节的计数
     */
    static $headLenth = 19;

    /**
     * 索引后缀
     */
    static $suffix = "%s_index.php";

    /**
     * @var
     * 索引文件的绝对路径
     */
    static $filePath;

    /**
     * 索引数组
     */
    static $index;


    /**
     * 构造函数
     * $cacheFileName 缓存文件名
     * $scene 场景 get||set
     */
    public function __construct($cacheFileName, $scene){
        parent::__construct($cacheFileName, $scene);
    }

    /**
     * 初始化数据, 生成索引文件
     */
    static function initData(){
        $indexIndex = static::indexIndex();
        $indexData = [];
        foreach($indexIndex as $index_k => $index_v){
            for($i = 0; $i < $index_v; $i++){
                $node = static::indexNote();
                $node["VL"] = $index_k;
                $indexData[$index_k][] = $node;
            }
        }
        static::upIndex($indexData);
    }

    /**
     * 定位写数据节点
     * $key 键值
     * $dataLength 数据序列化后的长度
     * $nodeData 要返回的数据
     */
    public function getWriteNode($key, $dataLength, &$nodeData){
        static::getIndex();
        $indexArr = &static::$index;
        $indexIndex = array_keys($indexArr);
        $_index = static::getCurIndex($indexIndex, $dataLength);
        if(!$_index) exit("The Cache Data Maxsize is 5M!");
        // 如果其他节点有此KEY,清空它
        $this->flushIndex($key, $_index);
        // 获取当前可以写的节点
        $curIndex = $indexArr[$_index];
        $nodeK = $this->__getWriteIndex($key, $curIndex);
        $nodeData = array(
            "index"  => &static::$index,
            "indexK" => $_index,
            "nodeK"  => $nodeK
        );
    }

    /**
     * 清空其他节点的KEY
     * $key 键值
     * $_index 当前可写入的节点索引
     */
    private function flushIndex($key, $_index){
        $indexArr = static::$index;
        foreach($indexArr as $indexArr_k => &$indexArr_v){
            if($indexArr_k == $_index) continue;
            foreach($indexArr_v as $node_k => &$node){
                if($node["K"] == $key){
                    $node["K"] = "";
                }
            }
        }
        static::upIndex($indexArr);
    }

    /**
     * 定位读数据节点
     * $key 键值
     */
    public function getReadNode($key){
        static::getIndex();
        $indexArr = static::$index;
        $node = "";
        foreach($indexArr as &$curIndex){
            foreach($curIndex as $curIndex_k => &$curIndex_v){
                if($curIndex_v["K"] == $key){
                    $node = $curIndex_v;
                    $curIndex_v["C"]++;
                    break;
                }
            }
        }
        // 更新索引的缓存命中次数
        static::upIndex($indexArr);
        return $this->__filterReadNode($node);
    }

    /**
     * 过滤数据节点
     * $node 查找到的索引节点
     */
    private function __filterReadNode($node){
        if(!$node) return false;
        $now = time();
        // 已经过期
        if(($node["E"] != -1) && (($node["CT"] + $node["E"])) < $now){
            return false;
        }
        return $node;
    }

    /**
     * 查找写节点
     * $key 键值
     * $curIndex 适合写入的挂载点，128 256……
     */
    private function __getWriteIndex($key, $curIndex){
        $nodeK = -1;
        $nullNodeK = -1;
        foreach($curIndex as $curIndex_k => $curIndex_v){
            if($curIndex_v["K"] == $key){
                $nodeK = $curIndex_k;
                break;
            }
            if(!$curIndex_v["K"] && $nullNodeK == -1){
                $nullNodeK = $curIndex_k;
            }
        }
        // 如果两个$node都不存在，就触发页面置换算法
        if($nodeK == -1 && $nullNodeK == -1){
            $nodeK = $this->__getLruNode($curIndex);
        }
        return ($nodeK > -1) ? $nodeK : $nullNodeK;
    }

    /**
     * 更新索引
     * $indexData 索引数组
     */
    static function upIndex(array $indexData){
        $content = static::$head;
        $indexData = serialize($indexData);
        $indexLenth = strlen($indexData);
        $content .= pack("L", $indexLenth) . $indexData;
        file::fileWrite(static::$filePath, $content);
    }

    /**
     * 获取完整索引数组
     */
    static function getIndex(){
        if(!static::$index){
            $packV = file::fileRead(static::$filePath, 4, 15);
            $lenth = unpack("L", $packV);
            $lenth = $lenth[1];
            $indexContent = file::fileRead(static::$filePath, $lenth, static::$headLenth);
            static::$index = unserialize($indexContent);
        }
        return true;
    }

    /**
     * 获取当前索引数组
     * 比如在128 还是256还是1M
     * $indexIndex 节点树
     * $dataLength 数据序列化后的长度
     */
    static function getCurIndex($indexIndex, $dataLength){
        // 怕数据乱，先排一次序
        sort($indexIndex);
        $_index = "";
        foreach($indexIndex as $indexIndex_v){
            if($dataLength <= $indexIndex_v){
                $_index = $indexIndex_v;
                break;
            }
        }
        return $_index;
    }

    /**
     * @param $curIndex
     * 当前缓存不够用，执行Lru算法
     * 简单的，1、如果有到期的，返回到期的，直接擦除到期的数据
     *         2、如果没有到期的，那就直接返回命中率最低的那条
     */
    private function __getLruNode($curIndex){
        // 先排序
        $nodeK = -1;
        $firstK = -1;
        $now = time();
        $gc = array_column($curIndex, "C");
        array_multisort($gc, $curIndex);
        foreach($curIndex as $curIndex_k => $curIndex_v){
            ($firstK == -1) && ($firstK = $curIndex_k);
            if(($curIndex_v["E"] != -1) && (($curIndex_v["CT"] + $curIndex_v["E"])) < $now){
                $nodeK = $curIndex_k;
                break;
            }
        }
        return ($nodeK > -1) ? $nodeK : $firstK;
    }

    /**
     * 返回索引文件的绝对路径
     */
    public function getFilePath(){
        return static::$filePath;
    }
}
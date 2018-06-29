<?php
/**
 * Created by MAQUEE.
 * author: MAQUEE  QQ:44257184
 * Date: 2018/6/27
 * Class file
 * 文件操作
 */

namespace macache\src;

class file {

    /**
     * 写数据
     * $filePath 文件的物理路径
     * $content 写入的内容
     * $seek 定位的位置
     */
    static function fileWrite($filePath, $content, $seek = 0){
        $handle = fopen($filePath, "r+");
        if(!is_writable($filePath)){
            exit("File is not writable!");
        }
        flock($handle, LOCK_EX);
        if($seek){
            fseek($handle, $seek);
        }
        fwrite($handle, $content);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * 读数据
     * $filePath 文件的物理路径
     * $lenth 要读取的长度
     * $seek 是否需要定位
     */
    static function fileRead($filePath, $lenth, $seek = 0){
        $handle = fopen($filePath, "r+");
        if($seek){
            fseek($handle, $seek);
        }
        $conten = fread($handle, $lenth);
        fclose($handle);
        return $conten;
    }

    /**
     * 获取文件的当前写入位置
     * $filePath 文件的物理路径
     */
    static function getWriteSeek($filePath){
        $handle = fopen($filePath, "r+");
        fseek($handle, 0, SEEK_END );
        $endSeek = ftell($handle);
        fclose($handle);
        return $endSeek;
    }
}
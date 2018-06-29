<?php
/**
 * Created by MAQUEE.
 * User: MAQUEE
 * Date: 2018/6/27
 * Time: 9:04
 * Class controller
 * 控制器接口
 */
namespace macache\src\inf;

interface controller {
    /**
     * 存数据
     */
    public function set($key, $data);

    /**
     * 取数据
     */
    public function get($key);
}

# maCache
file cache
说明：
    使用场景：当你没有服务器权限，又想使用redis或者macached的时候，你可以尝试用这个工具代替
    ==》本工具使用KEY => VALUE的形式存取数据
    ==》SET 参数："键名", "键值", "过期时间"
    ==》GET 参数："键名"
    ==》存取时可以指定缓存文件：maCache::instance("文件名")
    ==》缓存文件位置放在：本目录下的/data目录中，"文件名".php存的是数据，"文件名"_index.php存放索引文件
    ==》缓存设置：
        -->每个缓存文件，满存1700个KEY
        -->索引长度, 满存文件800M，1700个缓存
        -->128字节1000个     100M
        -->256的  500个      100M
        -->1M的   100个      100M
        -->5M的   100个      500M

    ==》简单的页面置换算法：优先替换过期的，其次替换命中次数最少的

测试：普通PC单机测试，1000 次写入查询，速度在2秒左右， 10万查询时间140S左右

使用：
    在合适的位置引入自动加载文件
    include "./maCache/maAutoLoader.php";
    maCache::instance()->get("键名", "键值", "过期时间");
    maCache::instance()->get("键名");

备注：
    本工具未经过严格测试，仅做学习交流使用

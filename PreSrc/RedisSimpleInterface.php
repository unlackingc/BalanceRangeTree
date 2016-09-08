<?php
/**
 * todo: 完成后添加过期删除机制
 * Created by PhpStorm.
 * User: unlockingc
 * Date: 16-4-14
 * Time: 上午11:44
 */

/**
 * 暂时没用，直接整合到AVL树中。
 * Class RedisSimpleInterface
 */
class RedisSimpleasdInterface
{
    public $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
}
<?php
namespace Tanbolt\Cookie;

/**
 * Interface CookieInterface
 * @package Tanbolt\Cookie
 */
interface CookieInterface
{
    /**
     * 配置 cookie 缺省项，参数值为 null，代表不修改当前当前配置
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return static
     */
    public function configure(string $domain = null, bool $secure = null, bool $httpOnly = null);

    /**
     * 设置 cookie 缺省 domain
     * @param ?string $domain
     * @return $this
     */
    public function setDefaultDomain(?string $domain);

    /**
     * 获取 cookie 缺省 domain
     * @return string
     */
    public function getDefaultDomain();

    /**
     * 设置 cookie 缺省 secure
     * @param bool $secure
     * @return $this
     */
    public function setDefaultSecure(bool $secure);

    /**
     * 获取 cookie 缺省 secure
     * @return bool
     */
    public function getDefaultSecure();

    /**
     * 设置 cookie 缺省 httpOnly
     * @param bool $httpOnly
     * @return $this
     */
    public function setDefaultHttpOnly(bool $httpOnly);

    /**
     * 获取 cookie 缺省 httpOnly
     * @return bool
     */
    public function getDefaultHttpOnly();

    /**
     * 恢复 domain secure httpOnly 为初始设置
     * @return $this
     */
    public function initialize();

    /**
     * 创建一个 cookie cake，参数值为 null，代表使用默认配置
     * @param ?string $name
     * @param ?string $value
     * @param ?string $maxAge
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return Cake
     */
    public function createCake(
        string $name = null,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 添加一个 cookie，$cake 可以是 Cake 对象或 字符串，其他参数值为 null，代表使用默认配置。
     * > 若 $cake 未 Cake 对象，会自动将后续参数配置到该 Cake 对象，最终返回 Cake 对象以便可以二次配置
     * @param string|Cake $cake
     * @param ?string $value
     * @param ?string $maxAge
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return Cake
     */
    public function add(
        $cake,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 设置一个永久有效的 cookie，参数值为 null，代表使用默认配置
     * @param string|Cake $cake
     * @param ?string $value
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return static
     */
    public function forever(
        $cake,
        string $value = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 设置一个 cookie 过期，参数值为 null，代表使用默认配置
     * @param string|Cake $cake
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return static
     */
    public function forget(
        $cake,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 判断是否设置过符合条件的 cookie，条件参数设置为 null 代表不限制
     * @param ?string $name
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return bool
     */
    public function has(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 获取指定条件的 cookie Cake 列表，条件参数设置为 null 代表不限制；
     * > 全部不限制则会返回所有已经设置 cookie Cake
     * @param ?string $name
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return Cake[]
     */
    public function get(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 移出符合条件的 cookie Cake，条件参数设置为 null 代表不限制
     * @param ?string $name
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return static
     */
    public function remove(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    );

    /**
     * 获取所有已设置的 cookie
     * @return Cake[]
     */
    public function all();

    /**
     * 清空所有已经设置的 cookie
     * @return static
     */
    public function clear();

    /**
     * 获取所有带发送 cookie
     * @return Cake[]
     */
    public function tobeSend();

    /**
     * 发送 Cookie 到客户端
     * @return static
     */
    public function send();
}

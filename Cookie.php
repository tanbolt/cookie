<?php
namespace Tanbolt\Cookie;

/**
 * Class Cookie: HTTP Cookie 管理器
 * @package Tanbolt\Cookie
 */
class Cookie implements CookieInterface
{
    /**
     * cookie Domain 默认设置
     * @var string
     */
    protected $defaultDomain = null;

    /**
     * cookie Secure 默认设置
     * @var bool
     */
    protected $defaultSecure = false;

    /**
     * cookie HttpOnly 默认设置
     * @var bool
     */
    protected $defaultHttpOnly = false;

    /**
     * 已设置的 cookie 项
     * @var Cake[]
     */
    protected $cookieCollection = [];

    /**
     * 测试数据
     * @var Cake[]
     */
    private $testCake = null;

    /**
     * Cookie constructor.
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     */
    public function __construct(string $domain = null, bool $secure = null, bool $httpOnly = null)
    {
        $this->configure($domain, $secure, $httpOnly);
    }

    /**
     * @inheritdoc
     */
    public function configure(string $domain = null, bool $secure = null, bool $httpOnly = null)
    {
        if (null !== $domain) {
            $this->setDefaultDomain($domain);
        }
        if (null !== $secure) {
            $this->setDefaultSecure($secure);
        }
        if (null !== $httpOnly) {
            $this->setDefaultHttpOnly($httpOnly);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultDomain(?string $domain)
    {
        $this->defaultDomain = $domain;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDomain()
    {
        return $this->defaultDomain;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultSecure(bool $secure)
    {
        $this->defaultSecure = $secure;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultSecure()
    {
        return $this->defaultSecure;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultHttpOnly(bool $httpOnly)
    {
        $this->defaultHttpOnly = $httpOnly;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultHttpOnly()
    {
        return $this->defaultHttpOnly;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->defaultDomain = null;
        $this->defaultSecure = false;
        $this->defaultHttpOnly = false;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createCake(
        string $name = null,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return (new Cake($name, $value, $maxAge, $path, $domain, $secure, $httpOnly))->__setCookie($this);
    }

    /**
     * @inheritdoc
     */
    public function add(
        $cake,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        if ($cake instanceof Cake) {
            if (null !== $value) {
                $cake->setValue($value);
            }
            if (null !== $maxAge) {
                $cake->setMaxAge($maxAge);
            }
            if (null !== $path) {
                $cake->setPath($path);
            }
            if (null !== $domain) {
                $cake->setDomain($domain);
            }
            if (null !== $secure) {
                $cake->setSecure($secure);
            }
            if (null !== $httpOnly) {
                $cake->setHttpOnly($httpOnly);
            }
        } else {
            $cake = null === $cake ? null : (string) $cake;
            $cake = $this->createCake($cake, $value, $maxAge, $path, $domain, $secure, $httpOnly);
        }
        $this->cookieCollection[] = $cake;
        return $cake;
    }

    /**
     * @inheritdoc
     */
    public function forever(
        $cake,
        string $value = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return $this->add($cake, $value, null, $path, $domain, $secure, $httpOnly)->forever();
    }

    /**
     * @inheritdoc
     */
    public function forget(
        $cake,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return $this->add($cake, null, null, $path, $domain, $secure, $httpOnly)->forget();
    }

    /**
     * @inheritdoc
     */
    public function has(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return count($this->getCakes($name, $path, $domain, $secure, $httpOnly)) > 0;
    }

    /**
     * @inheritdoc
     */
    public function get(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return array_values($this->getCakes($name, $path, $domain, $secure, $httpOnly));
    }

    /**
     * @inheritdoc
     */
    public function remove(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        foreach ($this->getCakes($name, $path, $domain, $secure, $httpOnly) as $key => $cake) {
            unset($this->cookieCollection[$key]);
        }
        $this->cookieCollection = array_values($this->cookieCollection);
        return $this;
    }

    /**
     * 获取指定条件的 cookie Cake 列表
     * @param ?string $name
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return Cake[]
     */
    protected function getCakes(
        string $name = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        if (null === $name && null === $path && null === $domain && null === $secure && null === $httpOnly) {
            return $this->cookieCollection;
        }
        $cakes = [];
        foreach ($this->cookieCollection as $key => $cake) {
            if (
                (null !== $name && $cake->getName() !== $name) ||
                (null !== $path && $cake->getPath() !== $path) ||
                (null !== $domain && $cake->getDomain() !== $domain) ||
                (null !== $secure && $cake->isSecure() !== $secure) ||
                (null !== $httpOnly && $cake->isHttpOnly() !== $httpOnly)
            ) {
                continue;
            }
            $cakes[$key] = $cake;
        }
        return $cakes;
    }

    /**
     * @inheritDoc
     */
    public function all($unique = true)
    {
        return $this->cookieCollection;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->cookieCollection = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function tobeSend()
    {
        // 翻转顺序, 后设置的 覆盖 先设置的
        $cakes = array_reverse($this->cookieCollection);
        $cookies = [];
        foreach ($cakes as $cake) {
            if (empty($cake->getName())) {
                continue;
            }
            // 过滤相同设置
            $unique = $cake->getName().$cake->getDomain().$cake->getPath().
                ($cake->isSecure() ? 1 : 0) . ($cake->isHttpOnly() ? 1 : 0);
            if (isset($cookies[$unique])) {
                continue;
            }
            $cookies[$unique] = $cake;
        }
        return array_values($cookies);
    }

    /**
     * @inheritDoc
     */
    public function send()
    {
        $cakes = $this->tobeSend();
        foreach ($cakes as $cake) {
            if (is_array($this->testCake)) {
                $this->testCake[] = $cake;
            } else {
                header('Set-Cookie: '. $cake, false);
            }
        }
        return $this;
    }

    /**
     * 单元测试 send method 时使用
     * @param bool $isTest
     * @return $this|Cake[]
     */
    public function __cakeTest(bool $isTest = true)
    {
        if (func_num_args()) {
            $this->testCake = $isTest ? [] : null;
            return $this;
        }
        return $this->testCake;
    }

    /**
     * 清除已设置 cake
     * @return $this
     */
    public function __destruct()
    {
        $this->cookieCollection = [];
        return $this;
    }
}

<?php
namespace Tanbolt\Cookie;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Class Cake: 单个 Cookie 对象
 * @package Tanbolt\Cookie
 */
class Cake
{
    /**
     * @var CookieInterface
     */
    protected $cookie;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int
     */
    protected $maxAge;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * @var string
     */
    protected $sameSite;

    /**
     * @var string
     */
    protected $priority;

    /**
     * Cake constructor.
     * @param ?string $name
     * @param ?string $value
     * @param ?string $maxAge
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     */
    public function __construct(
        string $name = null,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        $this->reset($name, $value, $maxAge, $path, $domain, $secure, $httpOnly);
    }

    /**
     * 设置 Cookie 对象，用于获取默认 Domain / secure / httpOnly
     * @param CookieInterface $cookie
     * @return $this
     */
    public function __setCookie(CookieInterface $cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * 一次性重置所有参数
     * @param ?string $name
     * @param ?string $value
     * @param ?string $maxAge
     * @param ?string $path
     * @param ?string $domain
     * @param ?bool $secure
     * @param ?bool $httpOnly
     * @return $this
     */
    public function reset(
        string $name = null,
        string $value = null,
        string $maxAge = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = null
    ) {
        return $this->setName($name)->setValue($value)->setMaxAge($maxAge)->setPath($path)
            ->setDomain($domain)->setSecure($secure)->setHttpOnly($httpOnly);
    }

    /**
     * 设置 name
     * @param ?string $name
     * @return $this
     */
    public function setName(?string $name)
    {
        if (null !== $name && preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
        $this->name = $name;
        return $this;
    }

    /**
     * 获取 name
     * @return ?string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 设置 value
     * @param ?string $value
     * @return $this
     */
    public function setValue(?string $value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * 获取 value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 通过 maxAge 设置过期时间，可以为 null，即仅在浏览器生命周期内有效
     * @param ?string $maxAge 支持 "100":100秒, "300s":200秒, "10h":10小时, "2d":2天
     * @return $this
     * @throws
     */
    public function setMaxAge(?string $maxAge)
    {
        if (empty($maxAge)) {
            $this->maxAge = 0;
            return $this;
        }
        $unit = strtolower(substr($maxAge, -1));
        switch ($unit) {
            case 'd':
                $maxAge = intval(substr($maxAge, 0, -1)) * 3600 * 24;
                break;
            case 'h':
                $maxAge = intval(substr($maxAge, 0, -1)) * 3600;
                break;
            case 's':
                $maxAge = intval(substr($maxAge, 0, -1));
                break;
            default:
                $maxAge = intval($maxAge);
                break;
        }
        $this->maxAge = $maxAge;
        return $this;
    }

    /**
     * 获取 max-age，返回数字，单位为 秒
     * @return int
     */
    public function getMaxAge()
    {
        return $this->maxAge ?: 0;
    }

    /**
     * 设置过期时间
     * @param DateTimeInterface|string|int|null $expire
     * @return $this
     * @throws
     */
    public function setExpire($expire)
    {
        if (empty($expire)) {
            $this->maxAge = 0;
        } elseif (is_int($expire)) {
            $this->maxAge = $expire - time();
        } else {
            if ($expire instanceof DateTimeInterface) {
                $date = clone $expire;
            } else {
                $date = new DateTime($expire);
            }
            $this->maxAge = $date->getTimestamp() - time();
        }
        return $this;
    }

    /**
     * 获取过期时间
     * @param bool $string
     * @return int|string
     */
    public function getExpire(bool $string = false)
    {
        $expire = $this->maxAge ? time() + $this->maxAge : 0;
        return $string ? ($expire ? gmdate(DATE_RFC7231, $expire) : '') : $expire;
    }

    /**
     * 设置为永不过期
     * @return $this
     */
    public function forever()
    {
        return $this->setMaxAge('730d');
    }

    /**
     * 设置为过期 cookie
     * @return $this
     */
    public function forget()
    {
        return $this->setMaxAge('-730d');
    }

    /**
     * 当前设置是否为删除 cookie
     * @return bool
     */
    public function isForget()
    {
        return $this->getMaxAge() < 0;
    }

    /**
     * 设置 path，设置为 null 则使用默认值
     * @param ?string $path
     * @return $this
     */
    public function setPath(?string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 获取 path, $nullable=false 会在 path=null (即未设置) 时返回缺省值 "/"
     * @param bool $nullable
     * @return ?string
     */
    public function getPath(bool $nullable = false)
    {
        return !$nullable && null === $this->path ? '/' : $this->path;
    }

    /**
     * 设置 domain
     * @param ?string $domain
     * @return $this
     */
    public function setDomain(?string $domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 获取 domain, $nullable=false 会在 domain=null (即未设置) 时返回缺省值
     * @param bool $nullable
     * @return ?string
     */
    public function getDomain(bool $nullable = false)
    {
        return !$nullable && null === $this->domain
            ? ($this->cookie ? $this->cookie->getDefaultDomain() : '')
            : $this->domain;
    }

    /**
     * 设置 secure (是否仅在 https 下有效)，设置为 null 则使用缺省值
     * @param ?bool $secure
     * @return $this
     */
    public function setSecure(?bool $secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * 获取 secure, $nullable=false 会在 secure=null 时返回缺省值
     * @param bool $nullable
     * @return ?bool
     */
    public function isSecure(bool $nullable = false)
    {
        return !$nullable && null === $this->secure
            ? ($this->cookie && $this->cookie->getDefaultSecure())
            : $this->secure;
    }

    /**
     * 设置 httpOnly (js 是否能获取 cookie)，设置为 null 则使用默认值
     * @param ?bool $httpOnly
     * @return $this
     */
    public function setHttpOnly(?bool $httpOnly)
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * 获取 httpOnly, $nullable=false 会在 httpOnly=null 时返回缺省值
     * @param bool $nullable
     * @return ?bool
     */
    public function isHttpOnly(bool $nullable = false)
    {
        return !$nullable && null === $this->httpOnly
            ? ($this->cookie && $this->cookie->getDefaultHttpOnly())
            : $this->httpOnly;
    }

    /**
     * 设置 SameSite 属性，设置为 null 则不使用该属性。可选值 "None" / "Lax" / "Strict"
     * @see https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Cookies#SameSite_attribute
     * @param string|null $sameSite
     * @return $this
     */
    public function setSameSite(?string $sameSite)
    {
        $this->sameSite = $sameSite;
        return $this;
    }

    /**
     * 获取 SameSite 属性
     * @return ?string
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * 设置 Priority 属性，这是一个新提案，目前 Chrome 已支持，可选值 "Low" / "Medium" / "High"
     * @see https://tools.ietf.org/html/draft-west-cookie-priority-00
     * @param ?string $priority
     * @return $this
     */
    public function setPriority(?string $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * 获取 Priority 属性
     * @return ?string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * 获取 Cake 所有属性的数组, Expires 以 Unix 时间戳形式返回，便于作为其他函数（如 setcookie）参数
     * @param false $withEmpty
     * @return array|false
     */
    public function toArray(bool $withEmpty = false)
    {
        if (empty($name = $this->getName())) {
            return false;
        }
        $cake = ['name' => urlencode($name)];
        if (empty($this->value)) {
            $cake['value'] = 'deleted';
            $cake['Expires'] = time() - 31536001;
        } else {
            $cake['value'] = urlencode($this->value);
            if ($expires = $this->getExpire()) {
                $cake['Expires'] = $expires;
            } elseif ($withEmpty) {
                $cake['Expires'] = 0;
            }
        }
        if (!empty($path = $this->getPath())) {
            $cake['Path'] = $path;
        } elseif ($withEmpty) {
            $cake['Path'] = '';
        }
        if (!empty($domain = $this->getDomain())) {
            $cake['Domain'] = $domain;
        } elseif ($withEmpty) {
            $cake['Domain'] = '';
        }
        if (!(empty($sameSite = $this->getSameSite()))) {
            $cake['SameSite'] = $sameSite;
        } elseif ($withEmpty) {
            $cake['SameSite'] = '';
        }
        if (!(empty($priority = $this->getPriority()))) {
            $cake['Priority'] = $priority;
        } elseif ($withEmpty) {
            $cake['Priority'] = '';
        }
        if ($this->isSecure()) {
            $cake['Secure'] = true;
        } elseif ($withEmpty) {
            $cake['Secure'] = false;
        }
        if ($this->isHttpOnly()) {
            $cake['HttpOnly'] = true;
        } elseif ($withEmpty) {
            $cake['HttpOnly'] = false;
        }
        return $cake;
    }

    /**
     * 字符串形式 cookie，未设置 name 属性会返回 '', 可以用在 header('Set-Cookie: cookie')
     * @return string The cookie
     */
    public function __toString()
    {
        $cake = $this->toArray();
        if (false === $cake) {
            return '';
        }
        $str = [$cake['name'].'='.$cake['value']];
        unset($cake['name'], $cake['value']);
        foreach ($cake as $key => $value) {
            if ('Expires' === $key) {
                $value = gmdate(DATE_RFC7231, $value);
            }
            $str[] = $key.(true === $value ? '' : '='.$value);
        }
        return implode('; ', $str);
    }
}

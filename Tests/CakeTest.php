<?php

use Tanbolt\Cookie\Cake;
use Tanbolt\Cookie\Cookie;
use PHPUnit\Framework\TestCase;

class CakeTest extends TestCase
{
    public function testConstruct()
    {
        $cake = new Cake();
        static::assertNull($cake->getName());
        static::assertNull($cake->getValue());
        static::assertEquals(0, $cake->getMaxAge());
        static::assertEquals(0, $cake->getExpire(false));
        static::assertEmpty($cake->getExpire());
        static::assertFalse($cake->isForget());

        static::assertNull($cake->getPath(true));
        static::assertEquals('/', $cake->getPath());

        static::assertNull($cake->getDomain(true));
        static::assertEmpty('', $cake->getDomain());

        static::assertNull($cake->isSecure(true));
        static::assertFalse($cake->isSecure());

        static::assertNull($cake->isHttpOnly(true));
        static::assertFalse($cake->isHttpOnly());

        static::assertNull($cake->getSameSite());
        $cake->reset('foo', 'bar', '2d', '/path', 'foo.com', true, true);
        $this->checkCakeAttr($cake);

        $cake = new Cake('foo', 'bar', '2d', '/path', 'foo.com', true, true);
        $this->checkCakeAttr($cake);
    }

    protected function checkCakeAttr(Cake $cake)
    {
        $maxAge = 2 * 24 * 3600;
        $expire = time() + $maxAge;
        static::assertEquals('foo', $cake->getName());
        static::assertEquals('bar', $cake->getValue());
        static::assertEquals($maxAge, $cake->getMaxAge());
        static::assertEquals($expire, $cake->getExpire());
        static::assertEquals(gmdate(DATE_RFC7231, $expire), $cake->getExpire(true));
        static::assertFalse($cake->isForget());
        static::assertEquals('/path', $cake->getPath(true));
        static::assertEquals('foo.com', $cake->getDomain(true));
        static::assertTrue($cake->isSecure(true));
        static::assertTrue($cake->isHttpOnly(true));
        static::assertNull($cake->getSameSite());
    }

    public function testSetName()
    {
        $cake = new Cake();
        static::assertNull($cake->getName());
        static::assertSame($cake, $cake->setName('foo2'));
        static::assertEquals('foo2', $cake->getName());
        $cake->setName(null);
        static::assertNull($cake->getName());
    }

    public function testSetValue()
    {
        $cake = new Cake();
        static::assertNull($cake->getValue());
        static::assertSame($cake, $cake->setValue('bar2'));
        static::assertEquals('bar2', $cake->getValue());
        $cake->setValue(null);
        static::assertNull($cake->getValue());
    }

    public function testSetMaxAge()
    {
        $now = time();
        $cake = new Cake();
        static::assertSame($cake, $cake->setMaxAge(2));
        static::assertEquals(2, $cake->getMaxAge());

        static::assertSame($cake, $cake->setMaxAge('2d'));
        static::assertEquals(2 * 3600 * 24, $cake->getMaxAge());
        static::assertEquals($now + 2 * 3600 * 24, $cake->getExpire());
        static::assertEquals(gmdate(DATE_RFC7231, $now + 2 * 3600 * 24), $cake->getExpire(true));

        static::assertSame($cake, $cake->setMaxAge('3h'));
        static::assertEquals(3 * 3600, $cake->getMaxAge());

        static::assertSame($cake, $cake->setMaxAge('99s'));
        static::assertEquals(99, $cake->getMaxAge());
        static::assertFalse($cake->isForget());

        static::assertSame($cake, $cake->setMaxAge(-2));
        static::assertEquals(-2, $cake->getMaxAge());

        static::assertSame($cake, $cake->setMaxAge('-2d'));
        static::assertEquals(-2 * 3600 * 24, $cake->getMaxAge());
        static::assertEquals($now - 2 * 3600 * 24, $cake->getExpire(false));
        static::assertEquals(gmdate(DATE_RFC7231, $now - 2 * 3600 * 24), $cake->getExpire(true));

        static::assertSame($cake, $cake->setMaxAge('-3h'));
        static::assertEquals(-3 * 3600, $cake->getMaxAge());

        static::assertSame($cake, $cake->setMaxAge('-99s'));
        static::assertEquals(-99, $cake->getMaxAge());
        static::assertTrue($cake->isForget());

        static::assertSame($cake, $cake->setMaxAge(2));
        static::assertFalse($cake->isForget());
        static::assertSame($cake, $cake->forget());
        static::assertTrue($cake->isForget());
        static::assertLessThan(0, $cake->getMaxAge());
    }

    public function testSetExpire()
    {
        $now = time();
        $cake = new Cake();
        static::assertSame($cake, $cake->setExpire('@'.($now + 10)));
        static::assertEquals($now + 10, $cake->getExpire());
        static::assertEquals(gmdate(DATE_RFC7231, $now + 10), $cake->getExpire(true));
        static::assertEquals(10, $cake->getMaxAge());

        $cake->setExpire(null);
        static::assertEquals(0, $cake->getMaxAge());
        static::assertEquals(0, $cake->getExpire(false));
        static::assertEmpty($cake->getExpire());

        static::assertSame($cake, $cake->setExpire($now + 20));
        static::assertEquals($now + 20, $cake->getExpire());
        static::assertEquals(gmdate(DATE_RFC7231, $now + 20), $cake->getExpire(true));
        static::assertEquals(20, $cake->getMaxAge());
    }

    public function testSetPath()
    {
        $cake = new Cake();
        static::assertEquals('/', $cake->getPath());
        static::assertNull($cake->getPath(true));
        static::assertSame($cake, $cake->setPath('/'));
        static::assertEquals('/', $cake->getPath());
        static::assertEquals('/', $cake->getPath(true));
        static::assertSame($cake, $cake->setPath('/foo'));
        static::assertEquals('/foo', $cake->getPath());
        static::assertEquals('/foo', $cake->getPath(true));
        $cake->setPath(null);
        static::assertEquals('/', $cake->getPath());
        static::assertNull($cake->getPath(true));
    }

    public function testSetDomain()
    {
        $cake = new Cake();
        static::assertEquals('', $cake->getDomain());
        static::assertNull($cake->getDomain(true));
        static::assertSame($cake, $cake->setDomain('test.io'));
        static::assertEquals('test.io', $cake->getDomain());

        $cake->setDomain(null);
        static::assertEquals('', $cake->getDomain());
        static::assertNull($cake->getDomain(true));

        $cake->__setCookie(new Cookie('foo.com'));
        static::assertEquals('foo.com', $cake->getDomain());
        static::assertNull($cake->getDomain(true));
    }

    public function testSetSecure()
    {
        $cake = new Cake();
        static::assertFalse($cake->isSecure());
        static::assertNull($cake->isSecure(true));

        static::assertSame($cake, $cake->setSecure(false));
        static::assertFalse($cake->isSecure());
        static::assertFalse($cake->isSecure(true));

        static::assertSame($cake, $cake->setSecure(true));
        static::assertTrue($cake->isSecure());
        static::assertTrue($cake->isSecure(true));

        $cake->setSecure(null);
        static::assertFalse($cake->isSecure());
        static::assertNull($cake->isSecure(true));

        $cookie = new Cookie(null, true);
        $cake->__setCookie($cookie);
        static::assertTrue($cake->isSecure());
        $cookie->setDefaultSecure(false);
        static::assertFalse($cake->isSecure());
    }

    public function testSetHttpOnly()
    {
        $cake = new Cake();
        static::assertFalse($cake->isHttpOnly());
        static::assertNull($cake->isHttpOnly(true));

        static::assertSame($cake, $cake->setHttpOnly(false));
        static::assertFalse($cake->isHttpOnly());
        static::assertFalse($cake->isHttpOnly(true));

        static::assertSame($cake, $cake->setHttpOnly(true));
        static::assertTrue($cake->isHttpOnly());
        static::assertTrue($cake->isHttpOnly(true));

        $cake->setHttpOnly(null);
        static::assertFalse($cake->isHttpOnly());
        static::assertNull($cake->isHttpOnly(true));

        $cookie = new Cookie(null, null, true);
        $cake->__setCookie($cookie);
        static::assertTrue($cake->isHttpOnly());
        $cookie->setDefaultHttpOnly(false);
        static::assertFalse($cake->isHttpOnly());
    }

    public function testSetSameSite()
    {
        $cake = new Cake();
        static::assertNull($cake->getSameSite());
        static::assertSame($cake, $cake->setSameSite('none'));
        static::assertEquals('none', $cake->getSameSite());

        $cake->setSameSite(null);
        static::assertNull($cake->getSameSite());
    }

    public function testSetPriority()
    {
        $cake = new Cake();
        static::assertNull($cake->getPriority());
        static::assertSame($cake, $cake->setPriority('LOW'));
        static::assertEquals('LOW', $cake->getPriority());

        $cake->setPriority(null);
        static::assertNull($cake->getPriority());
    }

    public function testToArray()
    {
        $cake = new Cake();
        static::assertFalse($cake->toArray());

        $cake->reset('foo', 'bar');
        static::assertEquals([
            'name' => 'foo',
            'value' => 'bar',
            'Path' => '/',
        ], $cake->toArray());

        static::assertEquals([
            'name' => 'foo',
            'value' => 'bar',
            'Path' => '/',
            'Expires' => 0,
            'Domain' => '',
            'Secure' => false,
            'HttpOnly' => false,
            'SameSite' => '',
            'Priority' => '',
        ], $cake->toArray(true));
    }
}

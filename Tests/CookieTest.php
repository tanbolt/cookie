<?php

use Tanbolt\Cookie\Cake;
use Tanbolt\Cookie\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function testCookieConfig()
    {
        $cookie = new Cookie('test.com', true, false);
        static::assertEquals('test.com', $cookie->getDefaultDomain());
        static::assertTrue($cookie->getDefaultSecure());
        static::assertFalse($cookie->getDefaultHttpOnly());

        $cookie->configure('foo.com', false, true);
        static::assertEquals('foo.com', $cookie->getDefaultDomain());
        static::assertFalse($cookie->getDefaultSecure());
        static::assertTrue($cookie->getDefaultHttpOnly());

        $cookie->configure('bar.com', true, true);
        static::assertEquals('bar.com', $cookie->getDefaultDomain());
        static::assertTrue($cookie->getDefaultSecure());
        static::assertTrue($cookie->getDefaultHttpOnly());
    }

    public function testDefaultDomain()
    {
        $cookie = new Cookie();
        static::assertEquals(null, $cookie->getDefaultDomain());
        static::assertSame($cookie, $cookie->setDefaultDomain('test.io'));
        static::assertEquals('test.io', $cookie->getDefaultDomain());
        static::assertSame($cookie, $cookie->setDefaultDomain(null));
        static::assertEquals(null, $cookie->getDefaultDomain());
    }

    public function testDefaultSecure()
    {
        $cookie = new Cookie();
        static::assertFalse($cookie->getDefaultSecure());
        static::assertSame($cookie, $cookie->setDefaultSecure(true));
        static::assertTrue($cookie->getDefaultSecure());
        static::assertSame($cookie, $cookie->setDefaultSecure(false));
        static::assertFalse($cookie->getDefaultSecure());
    }

    public function testDefaultHttpOnly()
    {
        $cookie = new Cookie();
        static::assertFalse($cookie->getDefaultHttpOnly());
        static::assertSame($cookie, $cookie->setDefaultHttpOnly(true));
        static::assertTrue($cookie->getDefaultHttpOnly());
        static::assertSame($cookie, $cookie->setDefaultHttpOnly(false));
        static::assertFalse($cookie->getDefaultHttpOnly());
    }

    public function testInitialize()
    {
        $cookie = new Cookie('test.com', true, true);
        static::assertEquals('test.com', $cookie->getDefaultDomain());
        static::assertTrue($cookie->getDefaultSecure());
        static::assertTrue($cookie->getDefaultSecure());

        static::assertSame($cookie, $cookie->initialize());
        static::assertEquals(null, $cookie->getDefaultDomain());
        static::assertFalse($cookie->getDefaultSecure());
        static::assertFalse($cookie->getDefaultHttpOnly());
    }

    public function testCreateCakeMethod()
    {
        $cookie = new Cookie();
        $cake = $cookie->createCake('foo', 'bar');
        static::assertCount(0, $cookie->all());
        static::assertEquals(0, $cake->getMaxAge());
        static::assertEquals(0, $cake->getExpire());
        static::assertEquals('', $cake->getExpire(true));
        $this->checkBasicCake($cake);

        $cake = $cookie->createCake('foo', 'bar', '10d', '/foo', 'test.com', true, true);
        static::assertEquals(10 * 3600 * 24, $cake->getMaxAge());
        $this->checkAdvanceCake($cake);
    }

    public function testAddMethod()
    {
        $cookie = new Cookie();
        $cake = $cookie->add('foo', 'bar');
        $cakes = $cookie->get('foo');
        static::assertCount(1, $cakes);
        static::assertSame($cake, $cakes[0]);
        static::assertEquals('bar', $cake->getValue());
        static::assertEquals(0, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkBasicCake($cake);

        $cake = $cookie->add('foo', 'bar', '10d', '/foo', 'test.com', true, true);
        static::assertEquals('bar', $cake->getValue());
        static::assertEquals(10 * 3600 * 24, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkAdvanceCake($cake);

        $cake = $cookie->createCake('foo', 'bar');
        static::assertSame($cake, $cookie->add($cake));

        $cake = $cookie->createCake('foo');
        $cookie->add($cake, 'bar', '10d', '/foo', 'test.com', true, true);
        static::assertEquals('bar', $cake->getValue());
        static::assertEquals(10 * 3600 * 24, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkAdvanceCake($cake);
    }

    public function testForeverMethod()
    {
        $cookie = new Cookie();
        $cake = $cookie->forever('foo', 'bar');
        $cakes = $cookie->get('foo');
        static::assertCount(1, $cakes);
        static::assertSame($cake, $cakes[0]);
        static::assertEquals('bar', $cake->getValue());
        static::assertGreaterThan(0, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkBasicCake($cake);

        $cake = $cookie->forever('foo', 'bar', '/foo', 'test.com', true, true);
        static::assertEquals('bar', $cake->getValue());
        static::assertGreaterThan(0, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkAdvanceCake($cake);

        $cake = $cookie->createCake('foo', 'bar');
        static::assertSame($cake, $cookie->forever($cake));

        $cake = $cookie->createCake('foo');
        $cookie->forever($cake, 'bar', '/foo', 'test.com', true, true);
        static::assertEquals('bar', $cake->getValue());
        static::assertGreaterThan(0, $cake->getMaxAge());
        static::assertFalse($cake->isForget());
        $this->checkAdvanceCake($cake);
    }

    public function testForgetMethod()
    {
        $cookie = new Cookie();
        $cake = $cookie->forget('foo');
        $cakes = $cookie->get('foo');
        static::assertCount(1, $cakes);
        static::assertSame($cake, $cakes[0]);
        static::assertLessThan(0, $cake->getMaxAge());
        static::assertTrue($cake->isForget());
        $this->checkBasicCake($cake);

        $cake = $cookie->forget('foo', '/foo', 'test.com', true, true);
        static::assertLessThan(0, $cake->getMaxAge());
        static::assertTrue($cake->isForget());
        $this->checkAdvanceCake($cake);

        $cake = $cookie->createCake('foo');
        static::assertSame($cake, $cookie->forever($cake));

        $cake = $cookie->createCake('foo');
        $cookie->forget($cake, '/foo', 'test.com', true, true);
        static::assertLessThan(0, $cake->getMaxAge());
        static::assertTrue($cake->isForget());
        $this->checkAdvanceCake($cake);
    }

    protected function checkBasicCake(Cake $cake)
    {
        static::assertInstanceOf(Cake::class, $cake);
        static::assertEquals('foo', $cake->getName());
        static::assertEquals('/', $cake->getPath());
        static::assertNull($cake->getDomain());
        static::assertFalse($cake->isSecure());
        static::assertFalse($cake->isHttpOnly());
    }

    protected function checkAdvanceCake(Cake $cake)
    {
        static::assertInstanceOf(Cake::class, $cake);
        static::assertEquals('foo', $cake->getName());
        static::assertEquals('/foo', $cake->getPath());
        static::assertEquals('test.com', $cake->getDomain());
        static::assertTrue($cake->isSecure());
        static::assertTrue($cake->isHttpOnly());
    }

    public function testCookieMange()
    {
        $cookie = new Cookie();
        $cookie->add('foo', 'bar');
        $cookie->add('foo', 'bar', 100, '/foo');
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com');
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com', true);
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com', true, true);

        static::assertTrue($cookie->has('foo'));
        static::assertTrue($cookie->has('foo', '/foo'));
        static::assertFalse($cookie->has('foo', '/hello'));

        static::assertFalse($cookie->has('hello'));
        $cookie->add('hello', 'world');
        static::assertTrue($cookie->has('hello'));

        static::assertCount(5, $cookie->get('foo'));
        static::assertCount(4, $cookie->get('foo', '/foo'));
        static::assertCount(3, $cookie->get('foo', '/foo', 'foo.com'));
        static::assertCount(2, $cookie->get('foo', '/foo', 'foo.com', true));
        static::assertCount(1, $cookie->get('foo', '/foo', 'foo.com', true, true));

        static::assertCount(6, $cookie->all());
        static::assertSame($cookie, $cookie->remove('foo', '/foo', 'foo.com', true, true));
        static::assertCount(5, $cookie->all());
        $cookie->remove('foo', '/foo', 'foo.com');
        static::assertCount(3, $cookie->all());
        $cookie->remove('foo');
        static::assertCount(1, $cookie->all());

        $cookie->add('h1', 'w1');
        $cookie->add('h2', 'w2');
        $cakes = $cookie->all();
        static::assertEquals('world', $cakes[0]->getValue());
        static::assertEquals('w1', $cakes[1]->getValue());
        static::assertEquals('w2', $cakes[2]->getValue());

        static::assertSame($cookie, $cookie->clear());
        static::assertCount(0, $cookie->all());
    }

    public function testSend()
    {
        $cookie = new Cookie();
        $cookie->__cakeTest(true);
        $cookie->add('foo', 'bar');
        $cookie->add('foo', 'bar', 100, '/foo');
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com');
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com', true);
        $cookie->add('foo', 'bar', 100, '/foo', 'foo.com', true, true);
        $cookie->add('foo', 'bar_new');
        $cookie->add('hello', 'world');
        $tobeSend = $cookie->tobeSend();
        static::assertSame($cookie, $cookie->send());
        $cakes = $cookie->__cakeTest();
        static::assertEquals($tobeSend, $cakes);
        static::assertCount(6, $cakes);
        static::assertEquals('foo', $cakes[1]->getName());
        static::assertEquals('bar_new', $cakes[1]->getValue());
    }
}

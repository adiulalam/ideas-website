<?php

declare(strict_types=1);

namespace Tests;

use App\Config;
use App\Http\HttpException;
use App\Http\LegacyRoutes;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Security\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class HttpTest extends TestCase
{
    public function testRouterDispatchesAndRejects(): void
    {
        $router = new Router();
        $router->get('/ideas/new', static fn (): Response => new Response('form'));
        $router->post('/ideas/new', static fn (): Response => new Response('saved'));

        self::assertSame('form', $router->dispatch('GET', '/ideas/new/')->body);
        self::assertSame('form', $router->dispatch('HEAD', '/ideas/new')->body);
        self::assertSame('saved', $router->dispatch('POST', '/ideas/new')->body);

        try {
            $router->dispatch('DELETE', '/ideas/new');
            self::fail('Expected 405');
        } catch (HttpException $e) {
            self::assertSame(405, $e->status);
            self::assertSame('GET, POST', $e->headers['Allow']);
        }

        $this->expectExceptionObject(new HttpException(404));
        $router->dispatch('GET', '/nope');
    }

    public function testLegacyUrlsRedirect(): void
    {
        self::assertSame('/login', LegacyRoutes::redirectFor('GET', '/views/auth/login/login.html.php', []));
        self::assertSame('/admin/authors', LegacyRoutes::redirectFor('GET', '/views/admin/author/', []));
        self::assertSame('/idea?id=12', LegacyRoutes::redirectFor('GET', '/', ['action' => 'Comment', 'ideaID' => '12']));
        self::assertNull(LegacyRoutes::redirectFor('GET', '/', ['action' => 'Comment', 'ideaID' => '1 OR 1=1']));
        self::assertNull(LegacyRoutes::redirectFor('POST', '/views/contact/', []));
    }

    public function testRequestCoercesHostileInput(): void
    {
        $request = new Request(
            ['id' => ['1'], 'page' => '2', 'q' => "a\0b"],
            ['categories' => ['1', 'x', '2', '2', ['3']], 'text' => ['array']],
            [],
            ['REQUEST_METHOD' => 'post', 'REQUEST_URI' => '/ideas/new?x=1'],
        );

        self::assertNull($request->queryInt('id'));
        self::assertSame(2, $request->queryInt('page'));
        self::assertSame('ab', $request->query('q'));
        self::assertSame([1, 2], $request->inputIntList('categories'));
        self::assertSame('', $request->input('text'));
        self::assertSame('POST', $request->method());
        self::assertSame('/ideas/new', $request->path());
    }

    public function testHttpsDetectionBehindProxies(): void
    {
        self::assertFalse((new Request())->isHttps());
        self::assertTrue((new Request([], [], [], ['HTTPS' => 'on']))->isHttps());
        self::assertTrue((new Request([], [], [], ['HTTP_X_FORWARDED_PROTO' => 'https']))->isHttps());
        self::assertTrue((new Request([], [], [], ['HTTP_CF_VISITOR' => '{"scheme":"https"}']))->isHttps());
    }

    public function testSecurityHeaders(): void
    {
        $headers = SecurityHeaders::for(true);

        $directives = [];
        foreach (explode(';', $headers['Content-Security-Policy']) as $directive) {
            $parts = preg_split('/\s+/', trim($directive)) ?: [];
            $directives[(string) array_shift($parts)] = $parts;
        }

        self::assertContains("'self'", $directives['script-src']);
        self::assertNotContains("'unsafe-inline'", $directives['script-src']);
        self::assertNotContains("'unsafe-eval'", $directives['script-src']);
        self::assertSame(["'none'"], $directives['object-src']);
        self::assertStringContainsString("frame-ancestors 'none'", $headers['Content-Security-Policy']);
        self::assertSame('nosniff', $headers['X-Content-Type-Options']);
        self::assertArrayHasKey('Strict-Transport-Security', $headers);
        self::assertArrayNotHasKey('Strict-Transport-Security', SecurityHeaders::for(false));
    }

    public function testLegacyHostPortIsSplitLikeTheOldDsn(): void
    {
        self::assertSame(['10.0.0.5', 3307], Config::splitHostPort('10.0.0.5:3307'));
        self::assertSame(['db.example.com', null], Config::splitHostPort('db.example.com'));
        self::assertSame(['db.example.com', null], Config::splitHostPort('db.example.com:'));
    }

    public function testCaptchaIsAlwaysOnInProduction(): void
    {
        self::assertTrue((new Config('/tmp', env: 'production'))->captchaEnabled());
        self::assertFalse((new Config('/tmp', env: 'development'))->captchaEnabled());
        self::assertTrue((new Config('/tmp', env: 'development', captchaSecretKey: 'x'))->captchaEnabled());
    }
}

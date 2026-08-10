<?php

namespace Tests\Router;

use Oladesoftware\Httpcrafter\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTestController {
    private string $greeting;

    public function __construct(string $greeting = 'Hello')
    {
        $this->greeting = $greeting;
    }

    public function sayHi(string $name): string
    {
        return $this->greeting . ', ' . $name . '!';
    }

    public function noArgs(): string
    {
        return 'no-args';
    }
}

class RouterTest extends TestCase
{
    protected function tearDown(): void
    {
        Router::removeInstance();
    }

    public function testInstanciationWithGetInstance()
    {
        $this->assertInstanceOf(Router::class, Router::getInstance());
        $router = Router::getInstance();
        $this->assertNotEmpty($router->pattern_types);
        $this->assertNotEmpty($router->target_separators);
        $this->assertNotEmpty($router->resolvers);
        $this->assertNotEmpty($router->placeholder_pattern);
        $this->assertNotEmpty($router->middleware_pattern);
        Router::removeInstance();
    }

    public function testInstanciationWithConstructor()
    {
        $this->assertInstanceOf(Router::class, new Router());
        $router = new Router();
        $this->assertNotEmpty($router->pattern_types);
        $this->assertNotEmpty($router->target_separators);
        $this->assertNotEmpty($router->resolvers);
        $this->assertNotEmpty($router->placeholder_pattern);
        $this->assertNotEmpty($router->middleware_pattern);
    }

    public function testInstanciationWithGetInstanceWithoutDefaults()
    {
        $router = Router::getInstance(false);
        $this->assertSame([], $router->pattern_types);
        $this->assertSame([], $router->target_separators);
        $this->assertSame([], $router->resolvers);
        $this->assertSame([], $router->placeholder_pattern);
        $this->assertSame([], $router->middleware_pattern);
        Router::removeInstance();
    }

    public function testInstanciationWithConstructorWithoutDefaults()
    {
        $router = new Router(false);
        $this->assertSame([], $router->pattern_types);
        $this->assertSame([], $router->target_separators);
        $this->assertSame([], $router->resolvers);
        $this->assertSame([], $router->placeholder_pattern);
        $this->assertSame([], $router->middleware_pattern);
    }

    public function testConstructorWithDefaultsPopulatesPlaceholderPattern()
    {
        $router = new Router();
        $this->assertSame(
            ['open', 'close', 'separator', 'name', 'type'],
            array_keys($router->placeholder_pattern)
        );
    }

    public function testGetInstanceWithDefaultsPopulatesPlaceholderPattern()
    {
        $router = Router::getInstance();
        $this->assertSame(
            ['open', 'close', 'separator', 'name', 'type'],
            array_keys($router->placeholder_pattern)
        );
        Router::removeInstance();
    }

    public function testConstructorWithDefaultsPopulatesResolvers()
    {
        $router = new Router();
        $this->assertSame(['callable', 'string', 'array'], array_keys($router->resolvers));
    }

    public function testGetInstanceWithDefaultsPopulatesResolvers()
    {
        $router = Router::getInstance();
        $this->assertSame(['callable', 'string', 'array'], array_keys($router->resolvers));
        Router::removeInstance();
    }

    public function testConstructorWithDefaultsPopulatesPatternTypes()
    {
        $router = new Router();
        $this->assertSame(['alpha', 'numeric', 'alphanum'], array_keys($router->pattern_types));
    }

    public function testGetInstanceWithDefaultsPopulatesPatternTypes()
    {
        $router = Router::getInstance();
        $this->assertSame(['alpha', 'numeric', 'alphanum'], array_keys($router->pattern_types));
        Router::removeInstance();
    }

    public function testConstructorWithDefaultsPopulatesTargetSeparators()
    {
        $router = new Router();
        $this->assertSame(['@'], $router->target_separators);
    }

    public function testGetInstanceWithDefaultsPopulatesTargetSeparators()
    {
        $router = Router::getInstance();
        $this->assertSame(['@'], $router->target_separators);
        Router::removeInstance();
    }

    public function testConstructorWithDefaultsPopulatesMiddlewarePattern()
    {
        $router = new Router();
        $this->assertSame(
            ['stack_order' => 'fifo', 'order' => 'group_first', 'separator' => '+'],
            $router->middleware_pattern
        );
    }

    public function testGetInstanceWithDefaultsPopulatesMiddlewarePattern()
    {
        $router = Router::getInstance();
        $this->assertSame(
            ['stack_order' => 'fifo', 'order' => 'group_first', 'separator' => '+'],
            $router->middleware_pattern
        );
        Router::removeInstance();
    }

    public function testGetInstanceReturnsSameInstance()
    {
        $first = Router::getInstance();
        $second = Router::getInstance();
        $this->assertSame($first, $second);
        Router::removeInstance();
    }

    public function testRemoveInstanceForcesNewInstance()
    {
        $first = Router::getInstance();
        Router::removeInstance();
        $second = Router::getInstance();
        $this->assertNotSame($first, $second);
        Router::removeInstance();
    }

    public function testConstructorCanInitSingleton()
    {
        $router = new Router(true, true);
        $this->assertSame($router, Router::getInstance());
        Router::removeInstance();
    }

    public function testGetInstanceIgnoresWithDefaultsArgumentWhenInstanceExists()
    {
        $first = Router::getInstance();
        $second = Router::getInstance(false);
        $this->assertSame($first, $second);
        $this->assertNotEmpty($second->pattern_types);
        Router::removeInstance();
    }

    public function testMatchReturnsEmptyArrayWhenNoRouteRegistered()
    {
        $router = new Router();
        $this->assertSame([], $router->match('GET', '/unknown'));
    }

    public function testMatchReturnsEmptyArrayWhenPathDoesNotMatch()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users');

        $this->assertSame([], $router->match('GET', '/does-not-exist'));
    }

    public function testMatchIsCaseInsensitiveOnMethod()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users');

        $result = $router->match('get', '/users');

        $this->assertNotEmpty($result);
        $this->assertSame(['GET'], $result['route']->methods);
    }

    public function testMatchReturnsRouteAndEmptyParamsForStaticPath()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users');

        $result = $router->match('GET', '/users');

        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertSame([], $result['params']);
    }

    public function testMatchExtractsSingleNamedParam()
    {
        $router = new Router();
        $router->get('/say-hello/{name:alpha}', fn (string $name) => $name);

        $result = $router->match('GET', '/say-hello/sophie');

        $this->assertSame(['name' => 'sophie'], $result['params']);
    }

    public function testMatchExtractsMultipleNamedParams()
    {
        $router = new Router();
        $router->get('/posts/{category:alpha}/{id:numeric}', fn () => null);

        $result = $router->match('GET', '/posts/tech/42');

        $this->assertSame(['category' => 'tech', 'id' => '42'], $result['params']);
    }

    public function testMatchReturnsFirstMatchingRouteWhenSeveralCouldMatch()
    {
        $router = new Router();
        $router
            ->get('/users/{id:numeric}', fn () => 'numeric', 'first')
            ->get('/users/{id:alphanum}', fn () => 'alphanum', 'second');

        $result = $router->match('GET', '/users/42');

        $this->assertSame('numeric', $router->run($result['route']->target));
    }

    public function testMatchRespectsGroupPrefix()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->get('/users', fn () => 'Users'),
        ]);

        $this->assertSame([], $router->match('GET', '/users'));
        $this->assertNotEmpty($router->match('GET', '/api/users'));
    }

    public function testHandleReturnsFalseWhenNoRouteMatches()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users');

        $this->assertFalse($router->handle('GET', '/unknown'));
    }

    public function testHandleReturnsFalseWhenMethodDoesNotMatch()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users');

        $this->assertFalse($router->handle('DELETE', '/users'));
    }

    public function testHandleResolvesCallableTarget()
    {
        $router = new Router();
        $router->get('/say-hello/{name:alpha}', fn (string $name) => 'Hello, ' . $name . '!');

        $this->assertSame('Hello, Sophie!', $router->handle('GET', '/say-hello/Sophie'));
    }

    public function testHandlePassesNamedParamsInDeclarationOrderAsArguments()
    {
        $router = new Router();
        $router->get(
            '/posts/{category:alpha}/{id:numeric}',
            fn (string $category, string $id) => "$category-$id"
        );

        $this->assertSame('tech-42', $router->handle('GET', '/posts/tech/42'));
    }

    public function testHandleResolvesStringTargetWithDefaultSeparator()
    {
        $router = new Router();
        $router->get('/hi/{name:alpha}', RouterTestController::class . '@sayHi');

        $this->assertSame('Hello, Bob!', $router->handle('GET', '/hi/Bob'));
    }

    public function testHandleResolvesArrayTargetWithConstructorArgs()
    {
        $router = new Router();
        $router->get(
            '/hi/{name:alpha}',
            [[RouterTestController::class, ['Hey']], 'sayHi']
        );

        $this->assertSame('Hey, Ana!', $router->handle('GET', '/hi/Ana'));
    }

    public function testHandleResolvesArrayTargetWithoutConstructorArgs()
    {
        $router = new Router();
        $router->get('/no-args', [RouterTestController::class, 'noArgs']);

        $this->assertSame('no-args', $router->handle('GET', '/no-args'));
    }

    public function testHandleThrowsWhenNoResolverMatchesTarget()
    {
        $router = new Router();
        $router
            ->removeResolver('callable')
            ->removeResolver('string')
            ->removeResolver('array');

        $router->get('/users', fn () => 'Users');

        $this->expectException(\RuntimeException::class);
        $router->handle('GET', '/users');
    }

    public function testPathReturnsEmptyStringForUnknownRouteName()
    {
        $router = new Router();
        $this->assertSame('', $router->path('unknown-route'));
    }

    public function testPathReturnsCompiledPathUntouchedForStaticRoute()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users', 'get.users');

        $this->assertSame('/users', $router->path('get.users'));
    }

    public function testPathSubstitutesSingleNamedParam()
    {
        $router = new Router();
        $router->get('/say-hello/{name:alpha}', fn () => null, 'say.hello');

        $this->assertSame('/say-hello/sophie', $router->path('say.hello', ['name' => 'sophie']));
    }

    public function testPathSubstitutesMultipleNamedParams()
    {
        $router = new Router();
        $router->get('/posts/{category:alpha}/{id:numeric}', fn () => null, 'post.show');

        $this->assertSame(
            '/posts/tech/42',
            $router->path('post.show', ['category' => 'tech', 'id' => '42'])
        );
    }

    public function testPathLeavesUnprovidedParamsAsCompiledRegex()
    {
        $router = new Router();
        $router->get('/posts/{category:alpha}/{id:numeric}', fn () => null, 'post.show');

        $path = $router->path('post.show', ['category' => 'tech']);

        $this->assertStringContainsString('/posts/tech/', $path);
        $this->assertStringContainsString('(?<id>', $path);
    }

    public function testPathAppendsQueryStringWhenQueriesProvided()
    {
        $router = new Router();
        $router->get('/users', fn () => 'Users', 'get.users');

        $this->assertSame(
            '/users?page=2&limit=10',
            $router->path('get.users', [], ['page' => 2, 'limit' => 10])
        );
    }

    public function testPathCombinesParamSubstitutionAndQueryString()
    {
        $router = new Router();
        $router->get('/say-hello/{name:alpha}', fn () => null, 'say.hello');

        $this->assertSame(
            '/say-hello/sophie?lang=fr',
            $router->path('say.hello', ['name' => 'sophie'], ['lang' => 'fr'])
        );
    }

    public function testPathIgnoresUnknownParamKeys()
    {
        $router = new Router();
        $router->get('/say-hello/{name:alpha}', fn () => null, 'say.hello');

        $path = $router->path('say.hello', ['not_a_param' => 'value']);

        $this->assertStringContainsString('(?<name>', $path);
    }

    public function testGroupAppliesPrefixToNestedRoutes()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users'),
        ]);

        $this->assertNotEmpty($router->match('GET', '/api/users'));
    }

    public function testNestedGroupsCombinePrefixesInOrder()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->group('/v1', [
                fn () => $router->get('/users', fn () => null, 'get.users'),
            ]),
        ]);

        $this->assertNotEmpty($router->match('GET', '/api/v1/users'));
    }

    public function testGroupPrefixStackIsPoppedAfterGroupCall()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users'),
        ]);
        $router->get('/outside', fn () => null, 'get.outside');

        $this->assertSame([], $router->match('GET', '/api/outside'));
        $this->assertNotEmpty($router->match('GET', '/outside'));
    }

    public function testGroupPrefixStackIsPoppedEvenWhenCallbackThrows()
    {
        $router = new Router();

        try {
            $router->group('/api', [
                function () {
                    throw new \RuntimeException('boom');
                },
            ]);
        } catch (\RuntimeException) {
        }

        $router->get('/outside', fn () => null, 'get.outside');

        $this->assertNotEmpty($router->match('GET', '/outside'));
    }

    public function testRouteWithoutOwnMiddlewareInheritsGroupMiddleware()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users'),
        ], 'token');

        $result = $router->match('GET', '/api/users');

        $this->assertSame('token', $result['route']->middleware);
    }

    public function testGroupMiddlewareCombinesWithRouteMiddlewareGroupFirstByDefault()
    {
        $router = new Router();
        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users', 'auth'),
        ], 'token');

        $result = $router->match('GET', '/api/users');

        $this->assertSame('token+auth', $result['route']->middleware);
    }

    public function testMiddlewareOrderPathFirstReversesCombination()
    {
        $router = new Router();
        $router->middleware_pattern = [
            'stack_order' => 'fifo',
            'order' => 'path_first',
            'separator' => '+',
        ];

        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users', 'auth'),
        ], 'token');

        $result = $router->match('GET', '/api/users');

        $this->assertSame('auth+token', $result['route']->middleware);
    }

    public function testNestedGroupMiddlewareCombinedInFifoOrderByDefault()
    {
        $router = new Router();
        $router->group('/outer', [
            fn () => $router->group('/inner', [
                fn () => $router->get('/route', fn () => null, 'nested.route'),
            ], 'inner'),
        ], 'outer');

        $result = $router->match('GET', '/outer/inner/route');

        $this->assertSame('outer+inner', $result['route']->middleware);
    }

    public function testNestedGroupMiddlewareCombinedInLifoOrderWhenConfigured()
    {
        $router = new Router();
        $router->middleware_pattern = [
            'stack_order' => 'lifo',
            'order' => 'group_first',
            'separator' => '+',
        ];

        $router->group('/outer', [
            fn () => $router->group('/inner', [
                fn () => $router->get('/route', fn () => null, 'nested.route'),
            ], 'inner'),
        ], 'outer');

        $result = $router->match('GET', '/outer/inner/route');

        $this->assertSame('inner+outer', $result['route']->middleware);
    }

    public function testMiddlewareSeparatorIsConfigurable()
    {
        $router = new Router();
        $router->middleware_pattern = [
            'stack_order' => 'fifo',
            'order' => 'group_first',
            'separator' => ',',
        ];

        $router->group('/api', [
            fn () => $router->get('/users', fn () => null, 'get.users', 'auth'),
        ], 'token');

        $result = $router->match('GET', '/api/users');

        $this->assertSame('token,auth', $result['route']->middleware);
    }

    public function testCompilePathThrowsForUnknownPatternType()
    {
        $router = new Router();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs('Unknown pattern type: slug');

        $router->get('/posts/{id:slug}', fn () => null);
    }

    public function testCompilePathLeavesPathLiteralWhenPlaceholderPatternEmpty()
    {
        $router = new Router();
        $router->clearPlaceholderPattern();

        $router->get('/say/{name:alpha}', fn () => 'ok', 'say.literal');

        $this->assertSame([], $router->match('GET', '/say/sophie'));
        $this->assertNotEmpty($router->match('GET', '/say/{name:alpha}'));
    }

    public function testCompilePathUsesCustomPatternType()
    {
        $router = new Router();
        $router->addPatternType('slug', '[a-z0-9-]+');

        $router->get('/posts/{slug:slug}', fn () => null, 'post.show');

        $result = $router->match('GET', '/posts/my-first-post');

        $this->assertSame(['slug' => 'my-first-post'], $result['params']);
    }

    public function testAddPatternTypeOverridesExistingTypeWithSameName()
    {
        $router = new Router();
        $router->addPatternType('alpha', '[A-Z]+');

        $router->get('/say/{name:alpha}', fn () => null, 'say.upper');

        $this->assertSame([], $router->match('GET', '/say/sophie'));
        $this->assertNotEmpty($router->match('GET', '/say/SOPHIE'));
    }
}
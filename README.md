# WordPress Plugin

A WordPress plugin scaffold built on the **Hoo WordPress Plugin Framework**, wired with [PHP-DI](https://php-di.org/). You declare hooks and routes in plain files; the framework registers them with WordPress and resolves your controllers (with their dependencies) out of the container.

> **Per-plugin namespace.** The global helpers (`hook()`, `route()`, …) and the `Application` (which holds the container, and is a static singleton) live in this plugin's namespace, so each plugin **must use a unique namespace** — that's what keeps two plugins' static state from colliding in the same WordPress install. You don't do this by hand: [`scripts/setup.php`](scripts/setup.php) rewrites the placeholder `Hoo\WordPressPlugin` namespace (and the plugin identifiers) for you. See [Creating a new plugin](#creating-a-new-plugin).

---

## Creating a new plugin

Scaffold a new plugin with Composer:

```bash
composer create-project hoo-lt/wordpress-plugin my-orders
```

When the install finishes, the `post-create-project-cmd` hook runs `scripts/setup.php`, which prompts you for (defaults derived from the directory name):

| Prompt | Example | Applied to |
|---|---|---|
| Plugin slug (text domain) | `my-orders` | text domain, log source, entry filename |
| PHP namespace | `MyOrders` (or `Acme\Orders`) | `src/`, helpers, `use function` lines, `composer.json` psr-4 |
| Composer package | `acme/my-orders` | `composer.json` `name` |
| Plugin display name | `My Orders` | the `Plugin Name:` header, this README |

The script then:

- rewrites `Hoo\WordPressPlugin` → your namespace everywhere (leaving the framework's `Hoo\WordPressPluginFramework` untouched),
- swaps the placeholder identifiers (`'wordpress-plugin'`, `wordpress_plugin_migrations`, plugin name),
- renames `wordpress-plugin.php` → `<slug>.php`,
- updates `composer.json`, then **deletes itself**.

Finish with:

```bash
composer dump-autoload
```

To re-run setup manually before committing (e.g. you took the defaults by accident): `php scripts/setup.php`.

---

## Directory layout

```
wordpress-plugin/
├── wordpress-plugin.php        # Entry point: plugin header + Application::boot()
├── composer.json
├── functions/
│   └── functions.php           # Global helpers: hook(), route(), controller(), action(), container(), dir(), file()…
├── container/
│   ├── container.php           # Builds the PHP-DI container from every definitions file
│   └── definitions/            # One bindings file per framework domain
│       ├── cache.php  database.php  http.php  pipeline.php  router.php …
├── hooks.php                   # Declare WordPress actions / filters / (de)activation
├── routes.php                  # Declare REST / admin-ajax / feed routes
├── src/                        # Your code: controllers, actions, services
│   └── Application/Application.php  # Boots the plugin; static holder for container, dir, file
├── views/                      # Templates rendered by the View factory
└── migrations/                 # Database migrations
```

## Installation

```bash
composer install
```

The helpers live in `functions/`, **one file per domain** (each in its own sub-namespace), and every file must be listed as a `files` autoload entry or its functions won't exist. The `Application` class is PSR-4 autoloaded from `src/`, so it needs no `files` entry:

```json
"autoload": {
    "psr-4": { "Hoo\\WordPressPlugin\\": "src/" },
    "files": [
        "functions/http/client.php",
        "functions/http/server.php",
        "functions/http/url.php",
        "functions/cache.php",
        "functions/database.php",
        "functions/view.php",
        "functions/functions.php"
    ]
}
```

> Drop a new helper file in `functions/` → add it to this `files` list → `composer dump-autoload`. (Unlike PSR-4 classes, `files` autoloads are **not** auto-discovered.)

After editing autoload config: `composer dump-autoload`.

## How it boots

`wordpress-plugin.php` is intentionally tiny:

```php
<?php
/** Plugin Name: WordPress Plugin */

defined('ABSPATH') || exit;

require __DIR__ . '/vendor/autoload.php';

Hoo\WordPressPlugin\Application\Application::boot(__DIR__, __FILE__);
```

`Application::boot()` builds the container, parks the `Application` as a static singleton (`Application::instance()`), then loads `hooks.php` and `routes.php`, builds them, and registers everything with WordPress. The instance holds the container, the plugin **dir**, and the plugin **file** — but you rarely touch it directly; you work in `hooks.php`, `routes.php`, and `src/`.

---

## Controllers & actions

Your handlers are plain classes in `src/`. They get **constructor dependency injection** for free — type-hint any bound interface and it's resolved from the container.

```php
namespace Hoo\WordPressPlugin\Controllers;

use Hoo\WordPressPluginFramework\Database\DatabaseInterface;
use Hoo\WordPressPluginFramework\Http\Server\Request\RequestInterface;
use Hoo\WordPressPluginFramework\Http\Server\Response\ResponseInterface;
use Hoo\WordPressPluginFramework\Http\Server\Response\ResponseFactoryInterface;

final class ItemsController
{
    public function __construct(
        private DatabaseInterface $database,
        private ResponseFactoryInterface $responses,
    ) {}

    public function index(RequestInterface $request): ResponseInterface
    {
        $items = $this->database->select('items')->all();

        return $this->responses->create(200, ['Content-Type' => 'application/json'], $items);
    }
}
```

Two helpers turn a class reference into a hook/route handler. **Both return a lazy closure** — the container resolves the class (and its constructor dependencies) only when WordPress actually fires the hook/route, not when `hooks.php`/`routes.php` run at load:

| Helper | Use for | Returns |
|---|---|---|
| `controller(Controller::class, 'method')` | classes with multiple methods | a closure that resolves the controller and calls `method(...)` on invocation |
| `action(Action::class)` | single-purpose **invokable** classes (a class with `__invoke()`) | a closure that resolves the class and calls `__invoke(...)` on invocation |

```php
controller(ItemsController::class, 'index')   // -> Closure: resolve ItemsController, call ->index() when fired
action(RegisterTaxonomies::class)             // -> Closure: resolve + call RegisterTaxonomies::__invoke when fired
```

**Every handler receives a `RequestInterface` as its first argument** — routes *and* hooks. The framework injects it before any other arguments (see [Action/Hook.php](../wordpress-plugin-framework/src/Hooker/Hooks/Action/Hook.php) / [Filter/Hook.php](../wordpress-plugin-framework/src/Hooker/Hooks/Filter/Hook.php), which call the handler as `($request, ...$args)`). So:

- **Route handler:** `index(RequestInterface $request): ResponseInterface`
- **Action handler:** `(RequestInterface $request, ...$hookArgs)` — request first, then the WordPress action args.
- **Filter handler:** `(RequestInterface $request, mixed $value, ...$hookArgs): mixed` — request first, then the filtered value (which you must return).

---

## Hooks — `hooks.php`

Return a hook builder chain. `hook()` gives you a fresh builder; every method returns a new builder, and `Application::boot()` calls `build()` for you.

```php
<?php

use function Hoo\WordPressPlugin\{hook, controller, action, file};

use Hoo\WordPressPlugin\Controllers\ContentController;
use Hoo\WordPressPlugin\Actions\RegisterTaxonomies;
use Hoo\WordPressPlugin\Actions\Activate;
use Hoo\WordPressPlugin\Actions\Deactivate;

return hook()
    ->action('init', action(RegisterTaxonomies::class))
    ->filter('the_content', controller(ContentController::class, 'append'), 20)
    ->activation(file(), action(Activate::class))
    ->deactivation(file(), action(Deactivate::class));
```

Builder methods:

| Method | Signature |
|---|---|
| `action` | `action(string $name, Closure $handler, int $priority = 10, ?Closure $middlewares = null)` |
| `filter` | `filter(string $name, Closure $handler, int $priority = 10, ?Closure $middlewares = null)` |
| `activation` | `activation(string $file, Closure $handler, ?Closure $middlewares = null)` |
| `deactivation` | `deactivation(string $file, Closure $handler, ?Closure $middlewares = null)` |

`file()` returns this plugin's main file — required by `activation`/`deactivation` so WordPress can key the hook to the plugin.

---

## Routes — `routes.php`

Same pattern with `route()`.

```php
<?php

use function Hoo\WordPressPlugin\{route, controller};

use Hoo\WordPressPlugin\Controllers\ItemsController;
use Hoo\WordPressPlugin\Controllers\FeedController;
use Hoo\WordPressPluginFramework\Http\Method\Method;

return route()
    ->rest('plugin/v1', '/items', controller(ItemsController::class, 'index'), Method::Get)
    ->rest('plugin/v1', '/items', controller(ItemsController::class, 'store'), Method::Post)
    ->adminAjax('save_item', controller(ItemsController::class, 'save'))
    ->feed('my_feed', controller(FeedController::class, 'render'));
```

Builder methods:

| Method | Signature |
|---|---|
| `rest` | `rest(string $namespace, string $route, Closure $handler, Method $method, ?Closure $middlewares = null)` |
| `adminAjax` | `adminAjax(string $action, Closure $handler, ?Closure $middlewares = null)` |
| `feed` | `feed(string $name, Closure $handler, ?Closure $middlewares = null)` |

`Method` is an enum: `Get`, `Head`, `Post`, `Put`, `Patch`, `Delete`, `Options`.

---

## Middlewares

Every hook and route method takes an optional **last argument**: a closure that receives a `MiddlewaresBuilder` and returns it. Middlewares wrap the handler in a pipeline (auth, validation, transactions, …) and run in the order declared.

```php
use Hoo\WordPressPluginFramework\Pipeline\Middlewares\MiddlewaresBuilder;

->rest(
    'plugin/v1',
    '/items',
    controller(ItemsController::class, 'store'),
    Method::Post,
    fn (MiddlewaresBuilder $mw) => $mw
        ->transaction(fn ($m) => $m)
        ->validate(fn ($v) => $v
            ->body('name',  fn ($r) => $r->string())
            ->body('email', fn ($r) => $r->string()->email())
        ),
)
```

Available middlewares:

| Method | Purpose |
|---|---|
| `transaction(Closure)` | wrap the handler in a database transaction |
| `logExecutionTime(Closure)` | log how long the handler took |
| `validate(Closure)` | validate request input (see below) |
| `currentUserCan(Closure)` | authorize via a capability |
| `verifyNonce(Closure)` | verify a WordPress nonce |

> ⚠️ `currentUserCan` and `verifyNonce` are constructed by the builder with no arguments and configured through their closure; confirm those middlewares expose the fluent configuration they expect before relying on them (see *Notes*).

### Validation

`validate()` receives a `ValidatorsBuilder`. Pick the input source by key, then describe its rules:

```php
->validate(fn ($v) => $v
    ->body('email',  fn ($r) => $r->string()->email())     // POST body field
    ->bodyQuery('q', fn ($r) => $r->nullable()->string())  // body or query
    ->query('page',  fn ($r) => $r->nullable()->int())     // query string
    ->header('X-Token', fn ($r) => $r->string())           // request header
    ->route('id',    fn ($r) => $r->int())                 // REST route param
)
```

Input sources: `body`, `bodyQuery`, `query`, `header`, `route`.

Rules (chainable on the rules builder `$r`): `array`, `bool`, `domain`, `email`, `enum(Enum::class)`, `float`, `int`, `ip`, `mac`, `nullable`, `regexp('/…/')`, `string`, `url`.

The builder also supports `condition(...)` and comparison validators (`compareInts`, `compareFloats`, `compareStrings`, `compareDateTimes`) for cross-field checks.

---

## Dependency injection

Bindings live in `container/definitions/`, **one file per framework domain**, filed by the domain that *implements* the interface (e.g. the logger binding is in `loggers.php`, not `woocommerce.php`). `container.php` globs and merges them all, so adding a domain is just dropping in a file.

Concrete classes autowire automatically — you only declare a binding when an **interface** needs a concrete, or a constructor needs a value PHP-DI can't guess:

```php
// container/definitions/app.php  (create this for your own bindings)
use function DI\autowire;

return [
    App\Service\PaymentGatewayInterface::class => autowire(App\Service\StripeGateway::class),
];
```

> Controllers/actions referenced via `controller()`/`action()` are resolved **lazily** — the container builds them (and their constructor dependencies) only when WordPress fires the hook/route, **not** when `hooks.php`/`routes.php` run at plugin load. `hooks.php`/`routes.php` just declare closures.

### Configuration values to change per plugin

These defaults are baked into the definition files — update them when you rename the plugin:

| File | Value |
|---|---|
| `container/definitions/loggers.php` | log source (`'wordpress-plugin'`) |
| `container/definitions/localization.php` | text domain (`'wordpress-plugin'`) |
| `container/definitions/repositories.php` | migrations option key |
| `container/definitions/database.php` | migrations path (`/migrations`) |
| `container/definitions/view.php` | views path (`/views`) |
| `container/definitions/cache.php` | cache TTL |
| `container/definitions/pipeline.php` | datetime comparison format |

---

## Helper reference (`functions/`)

Helpers are split across files by domain, each in its own sub-namespace. Import them with `use function` from the right namespace.

Booting is **not** a helper — it lives on the `Application` class: `Hoo\WordPressPlugin\Application\Application::boot(string $dir, string $file)`.

**`functions/functions.php` — `Hoo\WordPressPlugin`** (core)

| Helper | Returns |
|---|---|
| `container(): ContainerInterface` | the PHP-DI container |
| `dir(): string` | this plugin's root directory |
| `file(): string` | this plugin's main file path |
| `hook(): HooksBuilderInterface` | a fresh hook builder |
| `route(): RoutesBuilderInterface` | a fresh route builder |
| `controller(string $class, string $method): Closure` | lazy closure: resolves `$class`, calls `$method(...)` on invocation |
| `action(string $class): Closure` | lazy closure: resolves `$class`, calls `__invoke(...)` on invocation |

**Domain helpers** (each in its own namespace + file)

| Helper | Namespace / file | Returns |
|---|---|---|
| `cache(): CacheInterface` | `Hoo\WordPressPlugin\Cache` — `cache.php` | the cache service |
| `database(): DatabaseInterface` | `Hoo\WordPressPlugin\Database` — `database.php` | the database service |
| `view(string $view, ?ModelInterface $model = null): ViewInterface` | `Hoo\WordPressPlugin\View` — `view.php` | a rendered view |
| `client(): ClientInterface` | `Hoo\WordPressPlugin\Http\Client` — `http/client.php` | the outbound HTTP client |
| `request(string $method, string $url, ?array $headers = null, array\|string\|null $body = null): RequestInterface` | `Hoo\WordPressPlugin\Http\Client` — `http/client.php` | an outbound client request |
| `response(int $statusCode, ?array $headers = null, array\|string\|null $body = null): ResponseInterface` | `Hoo\WordPressPlugin\Http\Client` — `http/client.php` | an outbound client response |
| `request(string $method, string $url, ?array $headers = null, array\|string\|null $body = null): RequestInterface` | `Hoo\WordPressPlugin\Http\Server` — `http/server.php` | a server request |
| `response(int $statusCode, ?array $headers = null, array\|string\|null $body = null): ResponseInterface` | `Hoo\WordPressPlugin\Http\Server` — `http/server.php` | a server response |
| `url(string $url): UrlInterface` | `Hoo\WordPressPlugin\Http\Url` — `http/url.php` | a parsed URL |

> `Http\Client` and `Http\Server` both expose `request()`/`response()` with identical signatures but different return types — import the one you mean: `use function Hoo\WordPressPlugin\Http\Server\request;`.

---

## Notes

- **Namespace rename is mandatory** for multi-plugin safety — handled automatically by [`scripts/setup.php`](scripts/setup.php) when you `composer create-project`. See [Creating a new plugin](#creating-a-new-plugin).
- **`currentUserCan` / `verifyNonce`**: the `MiddlewaresBuilder` instantiates these middlewares with no constructor arguments and expects the closure to configure them fluently. If the underlying middleware still requires constructor arguments, that pairing needs reconciling before use.

### Orange Framework Runtime Core

`src/Application.php`
    Bootstraps env/config, seeds the DI container, and drives HTTP or CLI lifecycles while firing framework events.

`src/Container.php`
    Singleton DI container that registers values/closures/classes, autowires dependencies, resolves aliases, and promotes singleton services.

`src/Router.php`
    Maintains the route table, matches URI + verb to callbacks with parameter validation/caching, and generates URLs for named routes.

`src/Dispatcher.php`
    Bridges matched routes to controller methods via the container, decodes parameters, and enforces string responses.

`src/Input.php`
    Normalizes request data from superglobals/raw stream, supports method overrides, and exposes helpers for headers, segments, schemes, and request type.

`src/Output.php`
    Buffers body/headers/status, handles redirects or HTTPS enforcement, and flushes the final response (or exits) in a testable way.

### Support Services

`src/Config.php`
    Scans configured directories, merges environment-specific files (with optional caching), and offers array/object access to configuration.
`src/Event.php`
    Priority-based event bus for registering and triggering lifecycle hooks, with global enable/disable control.

`src/Error.php`
    Central error responder that populates data, picks environment/request-specific views or raw fallbacks, and pushes the response through Output.

`src/abstract/ViewAbstract.php`
`src/View.php`
    Provide the view-engine foundation—directory search, aliasing, dynamic view resolution, and rendered/cached templates.
`src/Data.php`
    Singleton ArrayObject shared store so services/controllers can share state via property-style access.

`src/interfaces`
    Declares contracts for core services (container, router, input, output, etc.) to keep implementations swappable and test-friendly.

### Ops & Infrastructure

`src/Security.php`
    Libsodium-backed toolkit for key generation, encryption/decryption, HMAC signatures, password hashing, and input sanitization.

`src/Log.php`
    PSR-3 compliant logger honoring configurable thresholds, delegating to injected handlers or its own file writer with safety checks.

`src/controllers/BaseController.php`
    Shared controller base class that wires config/input/output, autoloads declared services/libraries/helpers, and extends view search paths.

`src/controllers/HomeController.php`
    Default landing controller; swap it to customize the “/” route quickly.

`src/helpers/*`
    Grab bag of global utilities functions (atomic file writes, HTML builders, string/encoding helpers, escaping, etc.) used across the framework.

`src/config`
    Default configuration settings

`src/exceptions/*`
    framework-specific exception types (HTTP, router, container, filesystem, etc.) These extend OrangeException to make it easier to capture specific exceptions

`src/interfaces/*`
    framework-specific interfaces which should be extended to make replacing orange framework classses with your own.




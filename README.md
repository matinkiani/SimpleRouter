# Simple PHP Router

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Latest Version](https://img.shields.io/badge/version-v0.1.0-blue.svg)](https://github.com/yourusername/simple-router/releases)

A lightweight and simple PHP router inspired by Laravel's routing system. This is a personal project created for learning purposes and is not intended for production use yet.

## 🚀 Features

- Simple and intuitive API
- Support for GET, POST, PUT, PATCH, and DELETE methods
- Route parameters with named capture groups
- Route grouping with prefixes
- Middleware support (global and group-specific)
- Named routes
- Route parameter constraints
- PSR-4 compliant

## 📦 Installation

You can install the package via composer:

```bash
composer require matinkiani/simple-router
```

## 🔧 Usage

### Basic Routing

```php
use MatinKiani\SimpleRouter\Router;

$router = new Router();

// Define routes
$router->get('/', function() {
    return 'Hello World!';
});

$router->get('/users/{id}', function($id) {
    return "User {$id}";
})->name('user.show');

// Handle the request
$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
echo $response;
```

### Route Groups

```php
$router->group([
    'prefix' => '/admin',
    'middleware' => function($next) {
        // Check if user is admin
        if (!isAdmin()) {
            return 'Unauthorized';
        }
        return $next();
    }
], function() use ($router) {
    $router->get('/dashboard', function() {
        return 'Admin Dashboard';
    });
    
    $router->get('/users', function() {
        return 'Admin Users List';
    });
});
```

### Named Routes
* Note that dynamic parameters are not supported in named routes yet.

```php
$router->get('/posts', function() {
    return "Post";
})->name('post.show');

// Generate URL
$url = $router->route('post.show'); // Returns: /posts
```

### Middleware

```php
// Global middleware
$router->addGlobalMiddleware(function($next) {
    // Do something before
    $response = $next();
    // Do something after
    return $response;
});

// Route specific middleware
$router->get('/protected', function() {
    return 'Protected Content';
})->middleware(function($next) {
    if (!isAuthenticated()) {
        return 'Please login';
    }
    return $next();
});
```

## 🛣️ Roadmap

- [ ] Add support for dynamic parameters in named routes
- [ ] Add support for controllers
- [ ] Implement route caching
- [ ] Add regex pattern constraints for parameters
- [ ] Add optional parameters
- [ ] Add support for domain routing
- [ ] Add support for rate limiting
- [ ] Implement proper request/response objects
- [ ] Add proper error handling and custom error pages
- [ ] Add proper documentation
- [ ] Add support for dependency injection

## 🤝 Contributing

This is a personal project, but contributions are welcome! Feel free to:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the MIT license.

## ⚠️ Disclaimer

This router is inspired by Laravel's routing system but was built from scratch without checking Laravel's source code. It's currently in early development (v0.1.0) and is primarily a learning project. While you're free to use it, it's recommended to use established routing solutions for production applications.

## 🙏 Acknowledgments

- Inspired by Laravel's elegant routing system
- Built with love for the PHP community

## 📬 Contact

Made with ❤️ by Matin Kiani - Matinkianigame@gmail.com

---



# Laravel Searchable Scope

A powerful and flexible search trait for Laravel Eloquent models that makes implementing search functionality a breeze.

## Features

- Simple and intuitive API
- Configurable search behavior
- Support for related model searching
- Case-sensitive/insensitive search options
- Multiple search operators support
- Minimum search term length configuration
- Default search columns configuration

## Installation

You can install the package via composer:

```bash
composer require hamzaejaz/laravel-searchable-scope
```

The package will automatically register its service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="HamzaEjaz\SearchableScope\SearchableScopeServiceProvider" --tag="config"
```

This will create a `config/searchable-scope.php` file where you can modify the default settings.

## Usage

### Basic Usage

Add the trait to your model:

```php
use HamzaEjaz\SearchableScope\Traits\Searchable;

class User extends Model
{
    use Searchable;
}
```

Then you can search like this:

```php
// Search in all default columns
User::search('john')->get();

// Search in specific columns
User::search('john', ['name', 'email'])->get();

// Search in related models
User::search('john', [], ['posts' => ['title', 'content']])->get();
```

### Advanced Usage

You can define default searchable columns and relations in your model:

```php
class User extends Model
{
    use Searchable;

    protected $searchable = [
        'columns' => ['name', 'email', 'username'],
        'relations' => [
            'posts' => ['title', 'content'],
            'profile' => ['bio', 'location']
        ]
    ];
}
```

Now you can simply call:

```php
User::search('john')->get();
```

## Configuration Options

The package comes with several configuration options in `config/searchable-scope.php`:

- `default_operator`: The default search operator (LIKE, =, >, <, >=, <=)
- `case_sensitive`: Whether the search should be case sensitive
- `default_columns`: Default columns to search if none specified
- `min_term_length`: Minimum length of search term before search is performed

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

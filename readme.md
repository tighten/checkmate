# Checkmate

Checkmate is a tool that allows users to quickly see which Tighten projects are behind their prescribed Laravel version.

## Installation

1. Clone the repository locally
1. [Install dependencies](https://github.com/tightenco/checkmate/blob/master/.env.example) with `composer install`
1. Copy [`.env.example`](https://github.com/tightenco/checkmate/blob/master/.env.example) to `.env` and modify its contents to reflect your local environment
1. Make a new [Github token](https://github.com/settings/tokens/new) with the `repo` scope selected and save it in `.env` as `GITHUB_TOKEN`
1. Generate an application key via the terminal: `php artisan key:generate`
1. Create a database for the application and enter the database connection details in `.env`
1. Run the database migrations and seeders: `php artisan migrate --seed`
1. Install the frontend dependencies: `npm install`
1. Build the frontend dependencies: `npm run dev`
1. Configure a web server, such as the [built-in PHP web server](https://www.php.net/manual/en/features.commandline.webserver.php) or [Laravel Valet](https://laravel.com/docs/master/valet), to use the public directory as the document root
```
# For the built-in PHP web server:
php -S localhost:8080 -t public
```

## Usage
1. Run the following commands to populate the database:
    1. Import Laravel Versions: `php artisan sync:laravel-versions`
    1. Import projects: `php artisan sync:projects`
    1. Sync project versions: `php artisan sync:project-versions`
1. Visit the website in the browser

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tighten.co instead of using the issue tracker.

## Credits

- [mattstauffer](https://github.com/mattstauffer)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

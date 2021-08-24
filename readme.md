# Checkmate

Checkmate is a tool that allows users to quickly see which Laravel projects are behind their prescribed Laravel version.

## Installation

1. Clone the repository locally
1. Run `./bin/setup.sh`
1. Make a new [GitHub token](https://github.com/settings/tokens/new) with the `repo` and `read:org` scope selected and save it in `.env` as `GITHUB_TOKEN`
1. Create a database for the application and enter the database connection details in `.env`
1. Run `./bin/db.sh`
1. Configure a web server, such as the [built-in PHP web server](https://www.php.net/manual/en/features.commandline.webserver.php) or [Laravel Valet](https://laravel.com/docs/master/valet), to use the public directory as the document root

For the built-in PHP web server:
```bash
php -S localhost:8080 -t public
```

## Usage

1. Run the following commands to populate the database:
    1. Import Laravel Versions: `php artisan sync:laravel-versions`
    2. Import projects and their version details: `php artisan sync:projects`
    3. Visit the website in the browser

## Slackbot (optional)
Slackbot is configured to send a weekly (Friday, 6:00 am) list of your `Insecure` and `Behind` apps to a selected Slack channel. To set up:
1. Create an app in your Slack organization
2. Configure your Slack app for the channels you want to receive the notification.
3. Create an Incoming Webhook for your app
4. Paste the webhook as the `SHOW_PRIVATE_REPOS` value in your `.env` file

## Testing

```bash
vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tighten.co instead of using the issue tracker.

## Credits

- [marcusmoore](https://github.com/marcusmoore)
- [ctroms](https://github.com/ctroms)
- [mattstauffer](https://github.com/mattstauffer)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

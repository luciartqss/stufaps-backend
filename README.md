# Monitoring Backend

The backend for the **HEI Monitoring Subsystem** application. This app uses **Laravel Framework**.

## Prerequisite

1. [Git](https://git-scm.com/downloads)
2. [Xampp](https://www.apachefriends.org/download.html)
	- PHP v8.2 and above
	- MySQL
3. [Composer](https://getcomposer.org/download/)

## Setup

Clone the repository:

```
git clone https://<your-username>@bitbucket.org/chareze-hirang/romis-backend.git
```

Go to the project directory and install dependencies.

```
composer install --optimize-autoloader
```

Setup environment variables by editing the `.env` file.

| Key | Value | Description |
|---|---|---|
| APP_NAME | HEI_Monitoring | The name of the app. Any name will do on development.|
| APP_ENV | local | The current environment of the app. Use `local` for development.|
| APP_KEY | | The key used for encryptions of the app. Use `php artisan key:generate` command to create a random key. |
| APP_URL | http://localhost:8000 | The base url of the app. |
| FRONTEND_URL | http://localhost:5173 | The base url of the frontend app. |
| DB_\* | | The details about the database used. |
| MAIL_\* | | The details about the mailer used. (This is required for the mailing feature to work) Google SMTP works just fine.|
| REDIS_\*| | The details about the redis database used. (Leave it to default if redis is freshly installed) |
| SESSION_DOMAIN | localhost | The details about the redis database used. (Leave it to default if redis is freshly installed) |

## First Run
This is required in order to "seed" the database
```
php artisan migrate --seed
```

## Running Development Server

### Using PHP's built-in server

```
php -S <host>:<port> -t public
```

### Using Artisan Serve

```
php artisan serve
```

For more serving options run:

```
php artisan serve --help
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)
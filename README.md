# Overview

[`PHP v8.2`](https://php.net)

[`MySql v8.0`](https://github.com/laravel/sanctum)

[`Laravel v10`](https://github.com/laravel/laravel)

[`Laravel sanctum v3`](https://github.com/laravel/sanctum)

## Getting started

First, run command make `.env` file and install composer.

```bash
cp .env.example .env
```

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

Configure a shell alias.

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

```bash
sail up -d
```

```bash
sail artisan key:generate
```

Create database and insert data.

```bash
sail artisan migrate --seed
```

**Warning**: update email information to mailtrap or something to be safe in performing some features that send mail, avoid sending real email.

## Create service

Run command for make a service file. Ex: make `app/Services/UserService.php` file.

```bash
sail artisan app:make-service UserService
```

You can specify a model that your service depends on during creation by adding options `--model` or `-m`.

```bash
sail artisan app:make-service UserService --model=User
#OR
sail artisan app:make-service UserService -m User
```

## Create repository

Default Repository uses Eloquent, Run command make `app/Repositories/Eloquents/UserRepository.php` file
and interface `app/Repositories/Contracts/UserRepository.php`

```bash
sail artisan app:make-repository UserRepository
```

You can specify a model that your repository depends on during creation by adding options `--model` or `-m`.

```bash
sail artisan app:make-repository UserRepository -m User
```

Using difference repo for `Repository` during creation by adding options `--repo` or `-r`. For example, don't use Eloquent,
instead use the form of getting data from another service through the API. Now the repository will be created in the directory
`app/Repositories/APIs/UserRepository.php`

```bash
sail artisan app:make-repository UserRepository -m User -r APIs
```

After creating the repository remember to declare in `app/Providers/RepositoryServiceProvider.php` where `protected $repositories`

```php
protected $repositories = [
    ...
    \App\Repositories\Contracts\UserRepository::class => \App\Repositories\Eloquents\UserRepository::class,
]
```

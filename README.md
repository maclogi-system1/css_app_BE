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
cp docker/web/apache/default.apache.conf.example docker/web/apache/default.apache.conf
```

If you want to use ssl you must first update the `ENABLE_SSL=true` value in the `.env`. Then uncomment the code in the file `docker/web/apache/default.apache.conf`

```text
<VirtualHost *:443>
  ServerName maclogi_css.test
  DocumentRoot /var/www/html/public
  SSLEngine On
  SSLCertificateFile /etc/apache2/ssl/ssl.crt
  SSLCertificateKeyFile /etc/apache2/ssl/ssl.key
</VirtualHost>
```

Finally generate ssl certificate `docker/web/certs/ssl.crt` and `docker/web/certs/ssl.key`

```bash
./dockx up -d --build
```

```bash
./dockx composer install
```

```bash
./dockx artisan key:generate
```

Create database and insert data.

```bash
./dockx artisan migrate --seed
```

**Warning**: update email information to mailtrap or something to be safe in performing some features that send mail, avoid sending real email.

## Create service

Run command for make a service file. Ex: make `app/Services/UserService.php` file.

```bash
./dockx artisan app:make-service UserService
```

You can specify a model that your service depends on during creation by adding options `--model` or `-m`.

```bash
./dockx artisan app:make-service UserService --model=User
#OR
./dockx artisan app:make-service UserService -m User
```

## Create repository

Default Repository uses Eloquent, Run command make `app/Repositories/Eloquents/UserRepository.php` file
and interface `app/Repositories/Contracts/UserRepository.php`

```bash
./dockx artisan app:make-repository UserRepository
```

You can specify a model that your repository depends on during creation by adding options `--model` or `-m`.

```bash
./dockx artisan app:make-repository UserRepository -m User
```

Using difference repo for `Repository` during creation by adding options `--repo` or `-r`. For example, don't use Eloquent,
instead use the form of getting data from another service through the API. Now the repository will be created in the directory
`app/Repositories/APIs/UserRepository.php`

```bash
./dockx artisan app:make-repository UserRepository -m User -r APIs
```

After creating the repository remember to declare in `app/Providers/RepositoryServiceProvider.php` where `protected $repositories`

```php
protected $repositories = [
    ...
    \App\Repositories\Contracts\UserRepository::class => \App\Repositories\Eloquents\UserRepository::class,
]
```

## Git rules

### Create branch

---

Create a new branch for your task. Branches must be checked out from branch develop `git checkout -b <branch-name>`.

```bash
git checkout -b feature/MAC-1
```

### Branch name

---

The branch name must be in the format `<type>/MAC-<task-number>`

| &lt;type&gt;| Description                                                                   |
| :---------- | :---------------------------------------------------------------------------- |
| feature     | For new features, requests, corrections or additions.                         |
| bug         | For requests to find and fix bugs.                                            |
| hotfix      | For urgent bug finding and fixing requests. Usually an error from production. |

### Commit

---

The commit name must be in the format `MAC-<task-number>: <message>`

```bash
git commit -m "MAC-1: Sample message"
```

### Create pull request

---

First, rebase from the develop branch to get a latest code (`git pull --rebase origin develop`).
Then squash all commits into one commit before creating the `pull request`.

Example:

```bash
git pull --rebase origin develop
```

```bash
git log
```

We will see

```log
commit qyf70oe (HEAD -> feature/MAC-2, origin/feature/MAC-2)
Author: JohnDoe <johndoe@example.com>
Date:   Mon May 22 10:20:07 2023 +0700

    MAC-2: Second commit

commit p11ykz2
Author: JohnDoe <johndoe@example.com>
Date:   Fri May 19 13:30:00 2023 +0700

    MAC-2: Fisrt commit

commit gps4sw9 (origin/develop, develop)
Author: JohnDoe <johndoe@example.com>
Date:   Fri May 19 11:39:44 2023 +0700

    MAC-1: Develop commit
```

Reset to the latest commit of develop

```bash
git reset gps4sw9
```

Create a new commit and push

```bash
git add .
```

```bash
git commit -m "MAC-2: All commit"
```

Check log

```bash
git log
```

Now we can see that the MAC-2 commits have been merged into 1 as `MAC-2: All commit`

```log
commit ztlvra9 (HEAD -> feature/MAC-2, origin/feature/MAC-2)
Author: JohnDoe <johndoe@example.com>
Date:   Mon May 22 10:25:27 2023 +0700

    MAC-2: All commit

commit gps4sw9 (origin/develop, develop)
Author: JohnDoe <johndoe@example.com>
Date:   Fri May 19 11:39:44 2023 +0700

    MAC-1: Develop commit
```

And push

```bash
git push -f
```

Now we can make a pull request.

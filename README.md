# Todo List API

A RESTful API for managing personal to-do lists, built with PHP 8.4 and Yii3. Users sign in with JWT access tokens and rotating refresh tokens, and manage their own tasks.

This is a solution to the [Todo List API](https://roadmap.sh/projects/todo-list-api) in DDD presentation project from roadmap.sh.

## Requirements

- Docker with Docker Compose
- GNU Make

## Getting started

1. Set a JWT secret of at least 32 bytes in `docker/dev/.env`:

   ```bash
   echo "JWT_SECRET=$(openssl rand -hex 32)" >> docker/dev/.env
   ```

2. Build and start the containers:

   ```bash
   make build
   make up
   ```

3. Install dependencies and run the migrations:

   ```bash
   make composer install
   make yii migrate:up
   ```

The API is available at `http://localhost/api`.

## Configuration

| Setting | Where | Default |
|---|---|---|
| `JWT_SECRET` | `docker/dev/.env` | Required, at least 32 bytes |
| HTTP port | `DEV_PORT` in `docker/.env` | `80` |
| Token lifetimes | `config/common/params.php` | 15 minutes access, 30 days refresh |
| Database | `runtime/db.sqlite` | SQLite |

After changing `docker/dev/.env`, run `make up` again to recreate the container with the new values.

## Tests

```bash
make test
```

## Useful commands

| Command | Description |
|---|---|
| `make up` / `make down` | Start or stop the dev environment |
| `make shell` | Open a shell in the app container |
| `make yii <command>` | Run a Yii console command |
| `make composer <command>` | Run Composer |
| `make help` | List all commands |

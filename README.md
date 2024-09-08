# Setup Guide

## Requirements

- Docker engine
- Docker Compose
- Git Bash / Bash CLI

## Clone Repository

```
git clone https://github.com/mnabil1718/sukses-test.git
```

change directory into `sukses-test`. This depends on where your root repo folder is.

```
cd ./sukses-test
```

## Setup Commands

For automatic setup just run the `setup.sh` script. Make sure you are in root repo folder i.e. `sukses-test`

```
./setup.sh
```

or manually run commands inside the file manually

## Access Running App

Running app can be accessed in browser. For example to access get authors endpoint

```
http://localhost:8080/api/v1/authors
```

## Testing

Still inside `sukses-test` folder, run

```
cd ./sukses-test
```

```
docker-compose exec app php artisan test
```

Or to profile

```
docker-compose exec app php artisan test --profile
```

## Architecture Detail

detail explanation is provided at `Architecture.md`

## 1 Million Row

Performance optimization once the DB contains millions of row is at `Scaling` section in `Architecture.md`

## Deactivate

To shutdown and deactivate dev environment, cd into `sukses-test`

```
cd ./sukses-test
```

```
docker-compose down
```

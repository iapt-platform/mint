## Documents

- [Gin Web Framework](https://github.com/gin-gonic/gin)
- [PostgreSQL client and ORM for Golang](https://github.com/go-pg/pg)
- [Introduction to Object-relational mapping](https://pg.uptrace.dev/orm/intro/)
- [Redis client for Golang](https://github.com/go-redis/redis)
- [Go Standard library](https://golang.org/pkg/)

## SOP

- Gin: Using GET, POST, PUT, PATCH, DELETE
- Gin: Parameters in path
- Gin: Querystring parameters
- Gin: Model binding and validation
- Pg: Quickstart(**ingore createSchema**)
- Redis: Quickstart

## Hacking

1. Switch to current directory
   ```bash
   cd api/
   ```
2. Download go modules
   ```bash
   go mod download
   ```
3. Install [pre-commit](https://pre-commit.com/) and:
   > on Windows, you need to have `bash`
   ```bash
   # install git hooks
   pre-commit install
   # initialize git hooks
   pre-commit run
   ```
4. Run the server
   ```bash
   go run .
   # http://127.0.0.1:8080
   ```
5. Happy hacking!
   Your go code will be formatted automatically before you commit.

# README

This README would normally document whatever steps are necessary to get the
application up and running.

- Clone code
  
    ![git clone](documents/git-clone.png)

- [System dependencies](docker/)
  
    ```bash
    bundle install
    ```

- Database creation & initialization
    ![create database](documents/create-db.png)
  
    ```bash
    rake db:migrate
    ```

- Create a migration
  
    ```bash
    rails generate model Item --no-fixture --no-test-framework
    rails generate migration AddHiToUsers
    ```

- How to run the test suite

- Services (job queues, cache servers, search engines, etc.)

- Deployment instructions

## Documents

- [A Scope & Engine based, clean, powerful, customizable and sophisticated paginator for modern web app frameworks and ORMs](https://github.com/kaminari/kaminari)

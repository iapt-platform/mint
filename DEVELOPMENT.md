# Development

## For local dev

- Download [asdf](https://github.com/asdf-vm/asdf/releases) and put in into your `/usr/local/bin` folder.

- Install Nodejs & Php

```bash

sudo apt -y install dirmngr gpg curl gawk

asdf plugin add nodejs https://github.com/asdf-vm/asdf-nodejs.git
asdf install nodejs 24.11.1
node -v
npm -v

# https://github.com/asdf-community/asdf-php
sudo apt -y install plocate
asdf plugin add php https://github.com/asdf-community/asdf-php.git
asdf install php 8.5.0
php -v
```

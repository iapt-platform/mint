#!/bin/bash

set -e

echo 'setup zsh'
if [ ! -d "$HOME/.oh-my-zsh" ]
then
    git clone https://github.com/ohmyzsh/ohmyzsh.git $HOME/.oh-my-zsh
fi
if [ ! -f "$HOME/.zshrc" ]
then
    cp $HOME/.oh-my-zsh/templates/zshrc.zsh-template $HOME/.zshrc
    echo 'source $HOME/.profile' >> $HOME/.zshrc
fi

echo 'setup nodejs'
if [ ! -d "$HOME/.nvm" ]
then
    git clone https://github.com/nvm-sh/nvm.git $HOME/.nvm

    cat >> $HOME/.profile <<EOF
export NVM_DIR="\$HOME/.nvm"
[ -s "\$NVM_DIR/nvm.sh" ] && \. "\$NVM_DIR/nvm.sh"
[ -s "\$NVM_DIR/bash_completion" ] && \. "\$NVM_DIR/bash_completion" 
EOF
    echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.profile
fi

cd $HOME/.nvm
git checkout v0.39.1
. $HOME/.nvm/nvm.sh

if ! [ -x "$(command -v yarn)" ]
then
    nvm install node 
    nvm use node 
    npm install yarn -g
fi

echo 'setup php'
if [ ! -f "$HOME/.local/bin/composer" ]
then
    mkdir -p  $HOME/.local/bin
    wget -O $HOME/.local/bin/composer https://getcomposer.org/installer
    chmod +x $HOME/.local/bin/composer
fi

echo 'setup ssh'
if [ ! -d $HOME/.ssh ]
then
    mkdir $HOME/.ssh
    chmod 700 $HOME/.ssh
    cat /tmp/$USER.pub > $HOME/.ssh/authorized_keys
fi


echo 'setup workspace'
if [ ! -L $HOME/www ]
then
    ln -sf /workspace/www/$USER $HOME/www
fi

echo "done."

exit 0

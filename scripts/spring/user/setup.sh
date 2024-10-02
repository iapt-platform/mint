#!/bin/bash

set -e

echo 'setup zsh'
if [ ! -d "$HOME/.oh-my-zsh" ]
then
    # git clone https://github.com/ohmyzsh/ohmyzsh.git $HOME/.oh-my-zsh
    tar xf ohmyzsh.tar.xz -C $HOME
fi
if [ ! -f "$HOME/.zshrc" ]
then
    cp $HOME/.oh-my-zsh/templates/zshrc.zsh-template $HOME/.zshrc
    echo 'source $HOME/.profile' >> $HOME/.zshrc
fi

echo 'setup nodejs'
if [ ! -d "$HOME/.nvm" ]
then
    # git clone https://github.com/nvm-sh/nvm.git $HOME/.nvm
    tar xf nvm.tar.xz -C $HOME

    cat >> $HOME/.profile <<EOF
export NVM_DIR="\$HOME/.nvm"
[ -s "\$NVM_DIR/nvm.sh" ] && \. "\$NVM_DIR/nvm.sh"
[ -s "\$NVM_DIR/bash_completion" ] && \. "\$NVM_DIR/bash_completion" 
EOF
    echo 'export PATH=$HOME/.yarn/bin:$PATH' >> $HOME/.profile
    echo 'export EDITOR=vim' >> $HOME/.profile

    echo 'export http_proxy=socks5h://0:8000' >> $HOME/.profile
    echo 'export https_proxy=socks5h://0:8000' >> $HOME/.profile
    echo 'export ftp_proxy=socks5h://0:8000' >> $HOME/.profile
    
    echo 'export PATH=$HOME/.local/bin:$PATH' >> $HOME/.profile
fi

# cd $HOME/.nvm
# git checkout v0.39.3
# . $HOME/.nvm/nvm.sh

# if ! [ -x "$(command -v yarn)" ]
# then
#     nvm install node 
#     nvm use node 
#     npm install yarn -g
# fi

mkdir -p $HOME/.local/bin $HOME/local $HOME/downloads $HOME/tmp $HOME/workspace

echo 'setup php'
# if [ ! -f "$HOME/downloads/composer" ]
# then
#     wget -O $HOME/downloads/composer https://getcomposer.org/installer
# fi
if [ ! -f "$HOME/.local/bin/composer" ]
then
    # cd $HOME/downloads
    # php composer
    cp composer.phar $HOME/.local/bin/composer
fi

echo 'setup ssh'
if [ ! -d $HOME/.ssh ]
then
    mkdir $HOME/.ssh
    chmod 700 $HOME/.ssh
    cat $USER.pub > $HOME/.ssh/authorized_keys    
fi

echo 'setup vnc'
mkdir -p $HOME/.vnc
cat > $HOME/.vnc/xstartup <<EOF
#!/bin/sh

unset SESSION_MANAGER
exec openbox-session &
startlxqt &
EOF


echo 'setup workspace folder'
if [ ! -L $HOME/www ]
then
    ln -sf /srv/http/$USER $HOME/www
fi

echo 'setup tmux'
if [ ! -f $HOME/.tmux.conf ]
then
    echo 'set-option -g history-limit 102400' > $HOME/.tmux.conf
    echo 'set-option -g default-shell "/bin/zsh"' >> $HOME/.tmux.conf
fi

echo 'setup git'
git config --global core.quotepath false
git config --global http.version HTTP/1.1
git config --global pull.rebase false
git config --global http.proxy socks5h://0:8000

echo "done."

exit 0

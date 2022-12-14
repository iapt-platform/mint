# VSCode

## 全局配置文件地址

- Windows `%APPDATA%\Code\User\settings.json`
- macOS `$HOME/Library/Application Support/Code/User/settings.json`
- Linux `$HOME/.config/Code/User/settings.json`

## 远程 SSH 主机

- 按下 F1，选择 `Add New SSH Host`

  ![add new ssh host](by-ssh/1.png)

- 在这里输入自己的 ssh 地址（**ssh 默认去~/.ssh 下找 private key**）

  ![set up ssh](by-ssh/2.png)

- 上一部选择回车后，提示选择要保存的配置文件

  ![save to ssh config](by-ssh/3.png)

- 重新按 F1 然后这次选择 `Connect to Host`

  ![connect to host](by-ssh/4.png)

- 选择自己刚才添加的主机

  ![select configured ssh host](by-ssh/5.png)

- 然后 vscode 会打开新窗口，选择 `Open Folder`

  ![open folder](by-ssh/6.png)

- 在文件框内输入目录 然后点击 `OK`

  ![select folder](by-ssh/7.png)

- 还是选择 `Trust` 比较好，不愿意也行

  ![trust](by-ssh/8.png)

- 安装推荐插件(点击那个 Cloud 图表)

  ![plugins](by-ssh/10.png)

- 然后就可以欢快地写代码了 与 local 基本无差

  ![edit](by-ssh/9.png)

## ISSUES

- Permissions for XXX are too open().

  - For linux/macos user

    ```bash
    chmod 400 YOUR_PRIVATE_KEY_FILE
    ```

  - For windows user

    ![too open](by-ssh/too-open.gif)

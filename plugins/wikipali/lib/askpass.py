"""在没有终端的环境里，用操作系统的密码对话框读取密码。

为什么需要它：Claude Desktop、IDE 集成、agent 代跑等场景都没有 TTY，`getpass`
用不了。而让用户把密码打进对话、或放进命令行参数，都会让密码进入会话上下文或
`ps` / shell history——那是真正的泄漏。

系统对话框解决了这个矛盾：**密码由用户直接输给操作系统，不经过终端、不经过 argv、
调用方（包括 agent）全程看不到**，只从子进程的 stdout 拿回来，留在本进程内存里。

支持 zenity / kdialog（Linux）、osascript（macOS）、PowerShell（Windows）。
没有图形界面时返回 None，由调用方给出可操作的提示。
"""

import os
import shutil
import subprocess
import sys

DIALOG_TIMEOUT = 180


def gui_available():
    """当前环境有没有可用的图形界面。"""
    if sys.platform == 'darwin':
        return shutil.which('osascript') is not None
    if os.name == 'nt':
        return True
    if not (os.environ.get('DISPLAY') or os.environ.get('WAYLAND_DISPLAY')):
        return False
    return shutil.which('zenity') is not None or shutil.which('kdialog') is not None


def _run(cmd):
    """跑对话框命令，返回用户输入；取消或失败返回 None。

    stdout 只在本函数内流转，绝不打印——它装着密码。
    """
    try:
        proc = subprocess.run(cmd, capture_output=True, text=True, timeout=DIALOG_TIMEOUT)
    except (OSError, subprocess.TimeoutExpired):
        return None
    if proc.returncode != 0:
        return None
    return proc.stdout.rstrip('\n')


def ask(prompt, title='WikiPali', secret=True):
    """弹一个对话框问用户要一行输入。secret=True 时输入被遮蔽。

    返回字符串；用户取消、超时或没有图形界面时返回 None。
    """
    if sys.platform == 'darwin':
        hidden = ' with hidden answer' if secret else ''
        script = (f'display dialog "{prompt}" with title "{title}" '
                  f'default answer ""{hidden}')
        out = _run(['osascript', '-e', script, '-e',
                    'text returned of result'])
        return out

    if os.name == 'nt':
        if secret:
            ps = (f'$s = Read-Host -AsSecureString "{prompt}"; '
                  '[Runtime.InteropServices.Marshal]::PtrToStringAuto('
                  '[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))')
        else:
            ps = f'Read-Host "{prompt}"'
        return _run(['powershell', '-NoProfile', '-Command', ps])

    if shutil.which('zenity'):
        if secret:
            # --password 只给密码框；要用户名时用 --entry
            return _run(['zenity', '--password', '--title', f'{title} — {prompt}'])
        return _run(['zenity', '--entry', '--title', title, '--text', prompt])

    if shutil.which('kdialog'):
        flag = '--password' if secret else '--inputbox'
        return _run(['kdialog', '--title', title, flag, prompt])

    return None

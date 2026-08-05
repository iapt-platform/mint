#!/bin/sh
# 把本 Skill 整目录复制到目标项目或用户级 skills 目录。
#
#   ./install.sh ~/work/other-project   # 项目级，只在该项目激活
#   ./install.sh --user                 # 用户级，所有项目可用
#   ./install.sh --user --force         # 覆盖已存在的旧副本
#
# 只复制本目录自身，不碰仓库里的任何其他文件。副本不会自动更新——
# API 契约一改，旧副本就静默过期，靠 VERSION 判断是否该重装。

set -eu

SRC=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
NAME=$(basename -- "$SRC")
FORCE=0
TARGET=""

usage() {
    sed -n '2,10p' "$0" | sed 's/^# \{0,1\}//'
    exit "${1:-1}"
}

while [ $# -gt 0 ]; do
    case "$1" in
        --user) TARGET="$HOME/.claude/skills" ;;
        --force|-f) FORCE=1 ;;
        -h|--help) usage 0 ;;
        -*) echo "未知参数：$1" >&2; usage ;;
        *)
            if [ ! -d "$1" ]; then
                echo "错误：目标目录不存在：$1" >&2
                exit 1
            fi
            TARGET=$(CDPATH= cd -- "$1" && pwd)/.claude/skills
            ;;
    esac
    shift
done

if [ -z "$TARGET" ]; then
    echo "错误：请给出目标项目目录，或用 --user 装到用户级。" >&2
    usage
fi

DEST="$TARGET/$NAME"
VERSION=$(cat "$SRC/VERSION")

if [ "$DEST" = "$SRC" ]; then
    echo "错误：源和目标是同一个目录。" >&2
    exit 1
fi

if [ -d "$DEST" ]; then
    OLD="(无 VERSION 文件)"
    [ -f "$DEST/VERSION" ] && OLD=$(cat "$DEST/VERSION")
    if [ "$FORCE" -ne 1 ]; then
        echo "目标已存在：$DEST"
        echo "  已装版本：$OLD"
        echo "  本次版本：$VERSION"
        echo "要覆盖请加 --force。"
        exit 1
    fi
    echo "覆盖 $OLD → $VERSION"
    rm -rf "$DEST"
fi

mkdir -p "$TARGET"
cp -R "$SRC" "$DEST"
rm -rf "$DEST/__pycache__" "$DEST/scripts/__pycache__"
chmod +x "$DEST/scripts/wp.py" "$DEST/scripts/wp_login.py" "$DEST/install.sh"

echo "已安装 $NAME $VERSION 到 $DEST"
echo
echo "下一步（凭据在 ~/.wikipali/，多个副本共用，通常不必重新登录）："
echo "  python3 $DEST/scripts/wp.py whoami"

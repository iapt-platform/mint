# 数据库指南

## 语料库

## 字典


## 用户认证
### userinfo

#### `id`
INTEGER 唯一自增id 
#### `userid`
CHAR (36)  uuid
#### `path`
CHAR (36) 用户在服务器上私有文件的路径
#### `username`
TEXT (64) 用户登录名
#### `password`
TEXT 密码 md5加密
#### `nickname`
TEXT (64) 昵称
#### `email`
TEXT (256) 电邮地址
#### create_time
INTEGER 账户建立时间
#### `modify_time`
INTEGER 数据修改时间
#### `receive_time`
INTEGER 服务器收到此数据时间
#### `setting`
TEXT 用户设置 json 数据

### projile
用户简历
#### `id`
INTEGER 唯一自增id 
#### `user_id`
CHAR (36)  uuid
#### `bio`
TEXT 

## 用户字典

## 逐词解析

## 译文


## 工作组


## Channel


## 文章


## 权限管理



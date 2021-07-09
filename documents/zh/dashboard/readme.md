# 说明

本文档是网站页面的前端设计文档





# 网站地图
```mermaid
graph LR;

subgraph 藏经阁
home(藏经阁首页) --> palicanon(圣典)
home --> course(课程)
home --> dict(字典)
home --> collection(文集)
home --> term(术语百科)
collection --> article(文章)
article --> article-reader(文章阅读器)
dict -->|嵌入| reader
dict -->|嵌入| article-reader
palicanon --> reader(阅读器)
term -->|嵌入| reader

end

subgraph 译经楼
studio(译经楼首页) --> pali(经文选择)
studio --> filelist("逐词解析<br>编辑历史记录")
filelist --> wbw(逐词解析)
studio --> course1(课程管理)
studio --> channel(版本管理)
studio --> group(群组)
studio --> article1(文章管理)
studio --> collection1(文集管理)
end

home -->studio
dict -->|嵌入| wbw
reader -->wbw
term -->|嵌入| wbw
```
- 藏经阁-Libray
  - [首页](home.md)
  - [三藏](palicanon.md)  
  - [课程](course.md)
  - [字典](dict.md)
  - [搜索](search.md)
  - [个人空间](myzone.md)
- 译经楼-Studio
  - [首页](stu_home.md) 
  - [三藏分类](stu_pali.md)
  - [我的文档（最近打开）](stu_doc.md)
  - [逐词解析编辑](stu_wbw.md)
  - [协作文档](stu_coop.md)
  - [群组管理](stu_group.md)
  - [版本管理](stu_channel.md)
  - [课程管理](stu_course.md)
  - [单词本](stu_dict.md)
  - [百科词条](stu_term.md)
  - 统计数据
- 用户中心- User Center
  - [注册](sign_up.md)
  - [登陆](sign_in.md)
  - 找回密码
  - 个性化设置
- 实用工具
  - 佛历
  - 圣典编码转换
- 后台管理
  - 栏目内容管理
  - 用户管理
  - 数据表管理

# 藏经阁-Libray

## 首页

www.wikipali.org

### 已经实现的功能能

- 最新课程列表
- 最新百科词条列表

### 需要完善的功能

#### 最新课程列表

- 返回数量是写死的。应该在栏目后台里可以设置。

### 尚未实现的功能（目前的需求）

- **最新课程列表**-报名数字显示和报名功能

- 最新三藏译文列表

- 手机版 css
  - 写不同的 css 在手机端和 pc 看到不同的页面。
  - 不一定用 css 切换，可以使用不同的技术实现方式。

### 未来可能的发展

- 实现类似 tweet 的文章推荐功能。根据用户阅读行为推荐不同类型和内容的文章。
- 文章类型
  - 逐词解析单词
  - 词典词条
  - 百科词条
  - 句子
  - 段落
  - 章节
- 文章属性标签
  - 译文
  - 新手试验田
  - 雅正（希望大家提修改意见）
  - 求助
- 应用场景
  - 当有学习者不知道某个词的拆分，他在编辑器（studio）中单词的位置选择“求助”按钮。会发布一个带有“求助”标签的“逐词解析”类型的文章。某老师或网友看到求助文章，可以以跟帖的方式回答。


## 三藏分类

www.wikipali.org/palicanon

### 已经实现的功能能

- 章节标签过滤
- 在阅读器中打开章节

### 需要完善的功能

### 尚未实现的功能（目前的需求）

### 未来可能的发展

## 课程

### 已经实现的功能能

### 需要完善的功能

### 尚未实现的功能（目前的需求）

### 未来可能的发展

## 百科

### 已经实现的功能能

### 需要完善的功能

### 尚未实现的功能（目前的需求）

### 未来可能的发展

## 字典

## 标题搜索

## 全文搜索

## 黑体字搜索

## 经典阅读器

# 译经楼 Studio

## 欢迎页

## 经文选择

## 最近打开

## 协作

## 课程管理

## 用户字典管理

## 百科字典管理

## 工作组管理

# 用户中心

## 登录

## 注册

## 用户设置

# 网站后台管理

## 栏目管理

### 首页

### 三藏

### 课程

### 百科

### 字典

### 搜索

### 阅读器

### 用户管理

#### 修改权限

#### 禁用账号

### 数据表管理

#### 查看

#### 统计

#### 添加

#### 删除

# 数据表与模块关系

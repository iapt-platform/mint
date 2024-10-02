# 注意

- 该文档尚未完善，内容仅供参考
- 该文档中的技术方案尚未敲定，如涉及具体开发，需先行商议

# 说明

本文档是网站页面的前端设计文档

# 开发环境（待商议）

-   React
-   Umijs
-   Ant Design
-   Type Script

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

> 注意：以下目录内，仅标星（*）项目有具体内容，其余尚未编写

-   藏经阁-Libray
    -   [首页](home.md)
    -   [三藏*](palicanon.md)
    -   [课程](course.md)
    -   [字典](dict.md)
    -   [搜索](search.md)
    -   [个人空间](myzone.md)
-   译经楼-Studio
    -   [首页](stu_home.md)
    -   [三藏分类](stu_pali.md)
    -   [我的文档（最近打开）](stu_doc.md)
    -   [逐词解析编辑](stu_wbw.md)
    -   [协作文档](stu_coop.md)
    -   [群组管理](stu_group.md)
    -   [版本管理](stu_channel.md)
    -   [课程管理](stu_course.md)
    -   [单词本](stu_dict.md)
    -   [百科词条](stu_term.md)
    -   [文章](stu_article.md)
    -   [文集](stu_collection.md)
    -   统计数据
-   用户中心- User Center
    -   [注册](sign_up.md)
    -   [登陆](sign_in.md)
    -   找回密码
    -   个性化设置
-   实用工具
    -   佛历
    -   圣典编码转换
-   后台管理
    -   栏目内容管理
    -   用户管理
    -   数据表管理

# 调用后端 API

```mermaid
graph LR
home --> api_course
home --> api_term
home --> api_article
	subgraph 藏经阁-Libray
    	home[首页]
		palicanon[三藏]
		course[课程]
		dict[字典]
		search[搜索]
		myzone[个人空间]
	end

	subgraph API
	api_user[用户]
	api_channel[版本]
	api_term[术语]
	api_dict[字典]
	api_course[课程]
	api_group[工作组]
	api_palicanon[语料库]
	api_translation[译文]
	api_article[文章文集]
	api_wbw[逐词解析]
	api_search[全文搜索]
	api_history[浏览记录]
	api_others[其他工具表]
end

	subgraph 译经楼-Studio
		stu_home[首页]
		stu_pali[三藏分类]
		stu_doc[我的文档]
		stu_wbw[逐词解析编辑]
		stu_coop[协作文档]
		stu_group[群组管理]
		stu_channel[版本管理]
		stu_course[课程管理]
		stu_dict[单词本]
		stu_term[百科词条]
		stu_article[文章]
		stu_collection[文集]
		统计数据
		用户认证
	end

	subgraph 实用工具
		佛历
		圣典编码转换
	end


palicanon --> api_palicanon
palicanon --> api_term

course --> api_course
course --> api_user

dict --> api_dict
dict --> api_user

search --> api_palicanon

myzone --> api_user
myzone --> api_course

stu_pali --> api_palicanon

stu_doc --> api_history

stu_wbw --> api_wbw
stu_wbw --> api_palicanon

stu_group --> api_group

```

# 藏经阁-Libray

## 首页

www.wikipali.org

### 已经实现的功能能

-   最新课程列表
-   最新百科词条列表

### 需要完善的功能

#### 最新课程列表

-   返回数量是写死的。应该在栏目后台里可以设置。

### 尚未实现的功能（目前的需求）

-   **最新课程列表**-报名数字显示和报名功能

-   最新三藏译文列表

-   手机版 css
    -   写不同的 css 在手机端和 pc 看到不同的页面。
    -   不一定用 css 切换，可以使用不同的技术实现方式。

### 未来可能的发展

-   实现类似 tweet 的文章推荐功能。根据用户阅读行为推荐不同类型和内容的文章。
-   文章类型
    -   逐词解析单词
    -   词典词条
    -   百科词条
    -   句子
    -   段落
    -   章节
-   文章属性标签
    -   译文
    -   新手试验田
    -   雅正（希望大家提修改意见）
    -   求助
-   应用场景
    -   当有学习者不知道某个词的拆分，他在编辑器（studio）中单词的位置选择“求助”按钮。会发布一个带有“求助”标签的“逐词解析”类型的文章。某老师或网友看到求助文章，可以以跟帖的方式回答。

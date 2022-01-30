# 权限管理

## 业务需求

实现网站资源的权限管理。设置和查询某用户对某资源的拥有的权限。权限包括但不限于 转让，创建，修改，删除，读取等。

## 业务实现

分为三个部分
1. 角色管理-所有用户都至少有一个角色
2. 资源-所有资源都在一个资源树上资源树的根节点称为studio 也就是github的orginaze。
3. 权限设置-某角色对某资源有某种权限
4. 权限查询-某用户对某资源是否有某种权限
4. 资源列表-某用户列出某资源的直接子资源（有读取或更高权限）的列表

## 角色

每个studio 必定拥有下面的几种角色。
角色权限有从高到低的权限继承关系。高级角色必定有低级角色的所有权限。
角色由高到低分别是
1. 拥有者(owner)——一个资源的拥有者只限一个
	- 转让
1. 管理员(manager)
	- 分享权限给其他人(share)
3. 编辑者(editor)
	- 建立资源(create)
	- 修改资源(update)
	- 删除资源(delete)
4. 成员(member)
	- 读取(read)

每一个studio 建立的时候默认建立上述角色。

群组权限管理用新建角色的方法。每个群组在建立的时候，在该studio下新建一个角色。
此角色对任何资源都没有权限。需要手工指定某资源对这个群组的权限。添加成员实际上是将某user加到这个角色。

网站建立的时候默认建立一个"public" studio。
所有的注册用户都加入 public/member 角色。
所有访问网站者，包括未登录者，都加入 public/visitor 角色。
当某资源设置为全网公开，实际上是分享读取权限给public/visitor

## 资源
资源为树结构

- studio
	- channel
		- translation
		- orginal
		- nissaya
		- commentray
		- term
		- wbw
	- collection-templet
		- article-templet
			- sentence
	- user dictionary
	- group
	- course
		- lesson

网站建立时，建立根资源节点

- root
	- studio

user-01 账号建立时，同时建立studio-01.并在 root/studio 下面建立下面的父资源。

- studio-01
	- channel
	- collection-templet
	- user-dictionary
	- group
	- course

user-01 建立 channel-01 挂在 studio-01/channel下面

- root
	- studio
		- studio-01
			- channel
				- channel-01
## 授权

1. 注册用户user-01在注册时同时注册一个与用户名相同的studio-01。
1. 添加角色 public/member/user-01
1. 添加角色 studio-01/owner
1. 建立studio-01下所有默认资源根节点
1. 建立studio-01下所有默认角色
1. user-01与studio-01绑定。不能转让。
2. 设置user-01为 studio-01/owner。这样，此用户拥有该studio-01下全部权限。 
3. user-01 添加新的channel-01 为 在studio-01/channel 添加子资源 channel-01
4. user-01 将 channel-01 分享 给 user-02 = 授权 studio-01/channel/channel-01 给 studio-02/owner 

## 鉴权

- 只对角色进行鉴权操作
- 需要获取某角色在某资源节点下所有子资源列表(包括自己拥有的，共享资源等)
- 需要获取某角色在某资源下是否有某操作的权限
- 需要获取某用户（实际上是studio/owner角色）在某资源节点下所有子资源列表
- 需要获取某用户（实际上是studio/owner角色）在某资源下是否有某操作的权限


## 接口

每个api 有五个接口
1. **index** 列出资源列表(如：用户的所有文章)
1. **create** 创建资源
1. **show** 获取单个资源详细信息
1. **update** 修改资源
1. **delete** 删除资源

针对这五个接口实现鉴权函数。就是说在api::***中将会调用如下函数。

1. api::index
	- auth::Index($user,$resId,$resType)
1. api::create
	- auth::UserCanCreate($user,$resId,$resType)
	- auth::UserCteate($user,$resId,$resType)
1. api::show
	- auth::UserCanRead($user,$resId,$resType)
1. api::update
	- auth::UserCanUpdate($user,$resId,$resType)
	- auth::DeletePolicy($role,$resId,$resType,$act) // 删除授权
	- auth::CreatePolicy($role,$resId,$resType,$act) // 授权
1. api::delete
	- auth::DeleteObject($role,$resId,$resType) // 删除资源

## 场景模拟

### step-0 网站建立

网站建立时，建立根资源节点

- root
	- studio

### 注册新用户

1. 注册用户user-01在注册时同时注册一个与用户名相同的studio-01。
	- root
		- studio
			studio-01
1. 添加角色 public/member/user-01
1. 建立studio-01下所有默认资源根节点
	- studio-01
		- channel
		- collection-templet
		- user-dictionary
		- group
		- course
1. 建立studio-01下所有默认角色和继承关系
	- studio-01
		- owner
		- manager
		- editor
		- member
1. 将user-01与studio-01/owner 绑定
1. user-01与studio-01绑定。不能转让。


### 用户添加channel

user-01 建立 channel-01 挂在 studio-01/channel下面

- root
	- studio
		- studio-01
			- channel
				- channel-01

添加channel-01下面的资源根节点
- channel-01
	- translation
	- orginal
	- nissaya
	- commentray
	- term
	- wbw

>句子译文挂在 channel-01/translation下面。这样能够实现channel-01的信息如标题等和译文的修改分开鉴权。
比如我是channel-01的拥有者。我把channel-01的翻译权限给user-02。我希望user-02修改译文，而不是channel名称。
我只需分享channel-01/translation的edit权限给user-02。

### user-01修改channel信息
鉴权：user-01 是否 有channel写入权限
auth::UserCanUpdate($user-01,"channel-01","channel")


### 在channel下添加译文

鉴权：user-01 是否 有译文写入权限

auth::UserCanUpdate($user-01,"channel-01/translation","channel/translation")

### 分享channel
user-01将channel-01的翻译权限给user-02

auth::CreatePolicy("studio-02/owner","channel-01/translation","channel/translation","edit") // 授权

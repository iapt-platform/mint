<?php
require_once '../../vendor/autoload.php';
require_once '../config.php';

use Casbin\Enforcer;
use CasbinAdapter\Medoo\Adapter as DatabaseAdapter;

define("IDPrefixTranslation" , "it_");
define("IDPrefixChannel", "ic_");
define("IDPrefixArticle", "ia_");
define("IDPrefixUser" , "iu_");
define("IDPrefixOrg"  , "io_");
define("IDPrefixOrgGroup", "iog_");

// 资源分组：版本风格
define("ResStudio" , "s_studio");
// 资源分组：版本风格
define("ResChannel" , "s_channel");
// 资源分组：文章
define("ResArticle" , "s_article");
// 资源分组：文集
define("ResArticle" , "s_collection");
// 资源分组：译文（版本风格 + 文章）句子库
define("ResTranslation" , "s_translation");
// 资源分组：逐词译段落
define("ResArticle" , "s_wbw");
// 资源分组：术语
define("ResArticle" , "s_term");
// 资源分组：用户字典
define("ResArticle" , "s_userdict");

// 组织拥有者 转让
define("RoleOrgOwner" , "r_owner");
// 组织管理员 创建 删除 修改 文章/文集模版 channel group
define("RoleOrgAdmin" , "r_admin");
// 组织编辑 修改译文
define("RoleOrgEditor" , "r_editor");
// 组织成员 读取任意资源
define("RoleOrgMember" , "r_member");
// 组织访客（比如未注册用户） 只读取公开资源
define("RoleOrgVisitor" , "r_visitor");

// 权限：角色分组
define("GroupRole" , "g");
// 权限：资源分组
define("GroupRes" , "g2");

// 权限：阅读权限
define("PermRead" , "p_read");
// 权限：翻译权限
define("PermTrans" , "p_trans");
// 权限：修改权限
define("PermWrite" , "p_write");
// 权限：创建
define("PermCreate" , "p_create");
// 权限：删除
define("PermDelete" , "p_delete");
// 权限：修改
define("PermUpdate" , "p_update");

/*

所有的注册用户都加入 public/member 角色
所有访问网站者，包括未登录者，都加入 public/visitor 角色
注册用户在注册时同时注册一个与用户名相同的studio

*/

class CasbinAuth{
	protected $config = [
		'database_type' => Database["type"],
		'server' => Database['server'],
		'database_name' => Database['name'],
		'username' => Database['user'],
		'password' => Database['password'],
		'port' => Database['port'],
	];
	protected $adapter;
	protected $e;
	function __construct() {
		$this->adapter = DatabaseAdapter::newAdapter($config);
		$this->e = new Enforcer(__DIR__.'/rbac.model.conf', $adapter);
	}

	/*
	列出某用户有权限的资源列表
	例如 zhang3 在 org1 下的 所有 有权限的 channel 列表
	Index( "zhang3" , "org1" , "channel" )
	*/
	public function IndexRes($user,$resNodeId,$resType){

	}

	/*
	某用户某个资源列表
	例如 zhang3 在 org1 下的 所有 channel 列表
	Index( "zhang3" , "org1" , "channel" )
	*/
	public function UserCanReadRes($user,$resNodeId,$resType){

	}

	/*
	某用户建立某个资源
	*/
	public function UserCreateRes($user,$resNode,$resType){

	}
	/*
	查询某用户是否可以建立某个资源
	*/
	public function UserCanCreateRes($user,$resNode,$resType){

	}

	/*
	某用户删除某个资源
	*/
	public function UserDeleteRes($user,$resId,$resType){

	}
	/*
	查询某用户是否可以删除某个资源
	*/
	public function UserCanDeleteRes($user,$resId,$resType){

	}

	/*
	查询某用户是否可以修改某个资源
	返回值 true/fasle
	*/
	public function CanUpdateRes($user,$resId,$resType){

	}


	public function setRoleRight($role,$resId,$resType,$right){

	}
	public function testRoleRight($role,$resId,$resType,$right){
		
	}

	public function setUserRight($user,$resId,$resType,$right){

	}
	public function testUserRight($user,$resId,$resType,$right){
		
	}

	public function addRole($user,$role){

	}

	public function removeRole($user,$role){
		
	}
}

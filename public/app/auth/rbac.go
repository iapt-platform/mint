package main

import (
	"errors"
	"fmt"

	"github.com/casbin/casbin/v2"
	xormadapter "github.com/casbin/xorm-adapter/v2"
	"github.com/iapt-platform/config"
)

const (
	IDPrefixTranslation = "it_"
	IDPrefixChannel     = "ic_"
	IDPrefixArticle     = "ia_"
	IDPrefixUser        = "iu_"
	IDPrefixOrg         = "io_"
	IDPrefixOrgGroup    = "iog_"
	// 资源分组：版本风格
	ResChannel = "s_channel"
	// 资源分组：文章
	ResArticle = "s_article"
	// 资源分组：译文（版本风格 + 文章）
	ResTranslation = "s_translation"
	// 组织管理员
	RoleOrgAdmin = "r_admin"
	// 组织用户
	RoleOrgMember = "r_member"
	// 权限：角色分组
	GroupRole = "g"
	// 权限：资源分组
	GroupRes = "g2"
	// 权限：阅读权限
	PermRead = "p_read"
	// 权限：翻译权限
	PermTrans = "p_trans"
	// 权限：修改权限
	PermWrite = "p_write"
)

func CreateOrg(orgID string, userID string, e *casbin.Enforcer) {
	// 将该用户设置为组织机构的管理员
	e.AddNamedGroupingPolicy(GroupRole, IDPrefixUser+userID, RoleOrgAdmin, IDPrefixOrg+orgID)
	// 添加 admin 资源操作权限
	e.AddNamedPolicy("p", RoleOrgAdmin, IDPrefixOrg+orgID, ResArticle, ".*")
	e.AddNamedPolicy("p", RoleOrgAdmin, IDPrefixOrg+orgID, ResChannel, ".*")
	e.AddNamedPolicy("p", RoleOrgAdmin, IDPrefixOrg+orgID, ResTranslation, ".*")
	// 添加 member 资源操作权限
	e.AddNamedPolicy("p", RoleOrgMember, IDPrefixOrg+orgID, ResArticle, PermRead)
	e.AddNamedPolicy("p", RoleOrgMember, IDPrefixOrg+orgID, ResChannel, PermRead)
	e.AddNamedPolicy("p", RoleOrgMember, IDPrefixOrg+orgID, ResTranslation, PermRead)
}

func AddOrgMemberToGroup(orgID string, groupID string, userID string, e *casbin.Enforcer) {
	// g, li4, translator, org1
	e.AddNamedGroupingPolicy(GroupRole, IDPrefixUser+userID, IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID)
}

/*
 * 「为组织添加新成员」
 * 加入 member 分组，拥有组织资源读取权限
 */
func AddOrgMember(orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedGroupingPolicy(GroupRole, IDPrefixUser+userID, RoleOrgMember, IDPrefixOrg+orgID)
}

/*
 * 「为组织添加新管理员」
 * 加入 admin 分组，拥有组织资源一切权限
 */
func AddOrgAdmin(orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedGroupingPolicy(GroupRole, IDPrefixUser+userID, RoleOrgAdmin, IDPrefixOrg+orgID)
}

func CreateChannel(channelID string, orgID string, e *casbin.Enforcer) {
	// 将该 Channel 资源放入本组织的 channel 分组
	e.AddNamedGroupingPolicy(GroupRes, IDPrefixChannel+channelID, ResChannel, IDPrefixOrg+orgID)
}

/*
 * 「为版本风格添加只读用户」，也即是「分享版本风格」-「查看者」
 * 操作之后，该用户可以访问此版本风格下的所有译文
 */
func AddChannelReader(channelID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermRead)
}

func AddChannelReaderGroup(channelID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermRead)
}

/*
 * 「为版本风格添加翻译用户」，也即是「分享版本风格」-「编辑者」
 * 操作之后，该用户可以编辑此版本风格下的所有译文
 * 注意：该权限并不能编辑「版本风格」本身
 */
func AddChannelTranslator(channelID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermTrans)
}

func AddChannelTranslatorGroup(channelID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermTrans)
}

/*
 * 「为版本风格添加编辑用户」
 * 操作之后，该用户可以编辑此版本风格本身，比如：
 * 分享此版本风格、修改版本风格的描述等等
 * //TODO: 此功能是否需要？
 */
func AddChannelWriter(channelID string, orgID string, userID string, e *casbin.Enforcer) (string, error) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermWrite)
	return "", errors.New("Do we realy need this function?")
}

func AddChannelWriterGroup(channelID string, orgID string, groupID string, e *casbin.Enforcer) (string, error) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermWrite)
	return "", errors.New("Do we realy need this function?")
}

func CreateArticle(articleID string, orgID string, e *casbin.Enforcer) {
	// 将该 Article 资源放入本组织的 article 分组
	e.AddNamedGroupingPolicy("g2", IDPrefixArticle+articleID, ResArticle, IDPrefixOrg+orgID)
}

/*
 * 「为文章添加只读用户」，也即是「分享文章」-「查看者」
 * 操作之后，该用户可以访问此文章模板，
 * 对于能否查看对应译文，需要由对应 channel 的权限来判定
 */
func AddArticleReader(articleID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixArticle+articleID, PermRead)
}

func AddArticleReaderGroup(articleID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixArticle+articleID, PermRead)
}

/*
 * 「为文章添加编辑用户」，也即是「分享文章」-「编辑者」
 * 操作之后，该用户可以编辑此文章模板，
 * 对于能否查看、或修改对应译文，需要由对应 channel 的权限来判定
 */
func AddArticleWriter(articleID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixArticle+articleID, PermWrite)
}

func AddArticleWriterGroup(articleID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixArticle+articleID, PermWrite)
}

/*
 * 创建译文 = 版本风格 + 文章模板
 * 所谓 “译文是由 版本风格 和 文章模板 组成”，这是一个幻象。
 * 当我们参与翻译的时候，最终的翻译结果是对应的经文句子编号，如：{{125-2347-2-14}}，
 * 而不是 “翻译了这篇文章”，也就是说当前的翻译结果，可以在任何引用了该句子编号的文章里找到。
 *
 * 所以，与其说是 “创建了译文”，不如说是：
 * “通过在当前「文章模板」下基于某「版本风格」翻译该「句子编号」，
 * 导致该「文章模板」与该「版本风格」发生了「我曾经在该文章下使用该风格翻译了某句话」的关系”
 *
 * 那么，我们为什么要创建还要单独译文呢？
 * 1. 将「版本风格」与「文章模板」绑定，方便译者查找自己未完成的工作
 * 2. 单独创建的译文，可以单独分享，而不用分享整个「版本风格」加上单独分享「文章模板」
 * 2.1 如果单独分享了「版本风格」，那么该用户使用该风格翻译的其他「句子」也会被同时分享
 * 2.2 如果单独分享「文章模板」以及「版本风格」，两者并没有发生关联，
 *     那么被分享者也难以找到如此配对的资源
 *
 * 那么，如何判断当前访问的资源，是「文章对应的译文」还是「圣典对应的译文」呢？
 * 1. app/article?... 对应的资源便是「文章，以及对应的译文」
 * 2. app/reader?... 对应的资源便是「圣典，以及对应的译文」
 *
 * 那么，我们如何判断他们的权限呢？
 * 0. 如果以上两个资源都没有传递 channel 参数，则表示「不关特别联任何译文」
 *    则不需要做任何权限判断（可选择公开的版本风格显示译文）
 * 1. app/article?id=01&channel=01... article - 01 关联了 channel - 01
 * 1.1 首先该用户是否具有 channel - 01 和 article - 01 组成的译文的权限，如果有则授予
       这样以来，用户可以单独分享「译文」，而不必分享「版本风格」+「文章模板」了，
       同时也满足了「仅仅希望与其他同学一起编辑某一篇文章」的需求
 * 1.2 判断该用户是否拥有 article - 01 的权限，如果有，则进行 1.3 判断
 * 1.3 判断该用户是否具有 channel - 01 的权限，如果有，则授予，否则即没有权限
 * 2. app/reader?channel=01... 关联了 channel - 01
 * 2.1 判断该用户是否拥有 channel - 01 的权限即可
 *
 * 以上方法对于「写入译文」时的权限判断，同理可推得。推不得的话，我们讨论讨论。
*/
func CreateTranslation(channelID string, articleID string, orgID string, e *casbin.Enforcer) {
	// 将该 Translation 资源放入本组织的 Translation 分组
	// ID 由 channelID + articleID 构成
	e.AddNamedGroupingPolicy("g2", IDPrefixTranslation+channelID+"+"+articleID, ResTranslation, orgID)
}

/*
 * 「为译文添加只读用户」，也即是仅分享「某版本风格」对应的「某篇文章模板」
 * 操作之后，该用户可以访问此译文，但
 * 并不能：单独访问该「文章模板」，或，访问该「版本风格」对应的其他译文，
 * 以上两种权限不在此处授予。
 */
func AddTranslationReader(channelID string, articleID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermRead)
}

func AddTranslationReaderGroup(channelID string, articleID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermRead)
}

/*
 * 「为译文添加翻译用户」，也即是仅分享「某版本风格」对应的「某篇文章模板」，并允许翻译
 * 操作之后，该用户可以访问、修改此译文，但
 * 并不能：单独访问该「文章模板」，或，访问或修改该「版本风格」对应的其他译文，
 * 以上两种权限不在此处授予。
 */
func AddTranslationTranslator(channelID string, articleID string, orgID string, userID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermTrans)
}

func AddTranslationTranslatorGroup(channelID string, articleID string, orgID string, groupID string, e *casbin.Enforcer) {
	e.AddNamedPolicy("p", IDPrefixOrgGroup+groupID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermTrans)
}

/*
 * //TODO 是否有这个需求？
 * 好像不需要将译文的修改权限分享出去，用户需要的是翻译。
 * 即便分享出去，能做什么呢？解除绑定？
 */
func AddTranslationWriter(channelID string, articleID string, orgID string, userID string, e *casbin.Enforcer) (string, error) {
	return "", errors.New("Do we realy need this function?")
}

func AddTranslationWriterGroup(channelID string, articleID string, orgID string, groupID string, e *casbin.Enforcer) (string, error) {
	return "", errors.New("Do we realy need this function?")
}

func UserCanReadArticle(userID string, orgID string, articleID string, e *casbin.Enforcer) (bool, error) {
	return e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixArticle+articleID, PermRead+PermWrite)
}

func UserCanReadChannel(userID string, orgID string, channelID string, e *casbin.Enforcer) (bool, error) {
	return e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermRead+PermWrite+PermTrans)
}

func UserCanWriteChannel(userID string, orgID string, channelID string, e *casbin.Enforcer) (bool, error) {
	return e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermWrite)
}

func UserCanTranslateChannel(userID string, orgID string, channelID string, e *casbin.Enforcer) (bool, error) {
	return e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixChannel+channelID, PermTrans)
}

func UserCanReadTranslation(userID string, orgID string, channelID string, articleID string, e *casbin.Enforcer) (bool, error) {
	// 先基于译文判断
	r, err := e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermRead+PermTrans)
	if !r {
		// 再基于 版本风格 和 文章模板联合判断
		r_channel, _ := UserCanReadChannel(userID, orgID, channelID, e)
		r_article, _ := UserCanReadArticle(userID, orgID, articleID, e)
		if r_channel && r_article {
			return true, nil
		}
	}
	return r, err
}

func UserCanTranslateTranslation(userID string, orgID string, channelID string, articleID string, e *casbin.Enforcer) (bool, error) {
	// 先基于译文判断
	r, err := e.Enforce(IDPrefixUser+userID, IDPrefixOrg+orgID, IDPrefixTranslation+channelID+"+"+articleID, PermTrans)
	if !r {
		// 再基于 版本风格 和 文章模板联合判断
		r_channel, _ := UserCanTranslateChannel(userID, orgID, channelID, e)
		r_article, _ := UserCanReadArticle(userID, orgID, articleID, e)
		if r_channel && r_article {
			return true, nil
		}
	}
	return r, err
}

// ---------- Test Code ---------------

func testPermission(userID string, e *casbin.Enforcer) {
	fmt.Println("\n")

	fmt.Printf("// ----  %s 是否有权限 查看 组织 zhang3 的文章模板 article-01?", userID)
	r_a, _ := UserCanReadArticle(userID, "zhang3", "article-01", e)
	fmt.Printf("  %t\n", r_a)

	fmt.Printf("// ----  %s 是否有权限 查看 组织 zhang3 的版本风格 chinese-01?", userID)
	r_c, _ := UserCanReadChannel(userID, "zhang3", "chinese-01", e)
	fmt.Printf("  %t\n", r_c)

	fmt.Printf("// ----  %s 是否有权限 修改 组织 zhang3 的版本风格 chinese-01?", userID)
	r_c_w, _ := UserCanWriteChannel(userID, "zhang3", "chinese-01", e)
	fmt.Printf("  %t\n", r_c_w)

	fmt.Printf("// ----  %s 是否有权限 查看 组织 zhang3 的版本风格 chinese-02?", userID)
	r_c02, _ := UserCanReadChannel(userID, "zhang3", "chinese-02", e)
	fmt.Printf("  %t\n", r_c02)

	fmt.Printf("// ----  %s 是否有权限 修改 组织 zhang3 的版本风格 chinese-02?", userID)
	r_c_w02, _ := UserCanWriteChannel(userID, "zhang3", "chinese-02", e)
	fmt.Printf("  %t\n", r_c_w02)

	fmt.Printf("// ----  %s 是否有权限 基于 组织 zhang3 的版本风格 chinese-01 进行翻译?", userID)
	r_t, _ := UserCanTranslateChannel(userID, "zhang3", "chinese-01", e)
	fmt.Printf("  %t\n", r_t)

	fmt.Printf("// ----  %s 是否能查看 组织 zhang3 的译文 chinese-01+article-01?", userID)
	r_tt, _ := UserCanReadTranslation(userID, "zhang3", "chinese-01", "article-01", e)
	fmt.Printf("  %t\n", r_tt)

	fmt.Printf("// ----  %s 是否能翻译 组织 zhang3 的译文 chinese-01+article-01?", userID)
	r_tt1, _ := UserCanTranslateTranslation(userID, "zhang3", "chinese-01", "article-01", e)
	fmt.Printf("  %t\n", r_tt1)

	fmt.Printf("// ----  %s 是否能查看 组织 zhang3 的译文 chinese-02+article-01?", userID)
	r1_tt, _ := UserCanReadTranslation(userID, "zhang3", "chinese-02", "article-01", e)
	fmt.Printf("  %t\n", r1_tt)

	fmt.Printf("// ----  %s 是否能翻译 组织 zhang3 的译文 chinese-02+article-01?", userID)
	r1_tt1, _ := UserCanTranslateTranslation(userID, "zhang3", "chinese-02", "article-01", e)
	fmt.Printf("  %t\n", r1_tt1)

	fmt.Println("\n")
}

func main() {
	// 获取数据库配置
	dbConfig := config.GetConfig().Database
	dataSourceName := fmt.Sprintf("user=%s password=%s host=%s port=%s sslmode=%s",
		dbConfig.Username,
		dbConfig.Password,
		dbConfig.Host,
		dbConfig.Port,
		dbConfig.SSLMode)
	// 初始化 Casbin，默认使用数据库 `casbin`，如果不存在，则创建新数据库 `casbin`
	a, _ := xormadapter.NewAdapter("postgres", dataSourceName)
	// 加载权限模型
	e, _ := casbin.NewEnforcer("./rbac/rbac_model.conf", a)
	// e.AddNamedMatchingFunc("g", "", util.RegexMatch)
	// e.AddNamedDomainMatchingFunc("g", "", util.RegexMatch)
	// 从数据库加载已定义的权限策略
	e.LoadPolicy()

	/*
	 * 按照业务逻辑测试验证
	 */

	fmt.Println("// 创建新用户 zhang3，等于同时创建了 Org: zhang3，只是 OrgID 和 UserID 相同")
	CreateOrg("zhang3", "zhang3", e)

	fmt.Println("// 用户 zhang3 创建了 版本风格 chinese-01")
	CreateChannel("chinese-01", "zhang3", e)

	fmt.Println("// 用户 zhang3 创建了 版本风格 chinese-02")
	CreateChannel("chinese-02", "zhang3", e)

	fmt.Println("// 用户 zhang3 在组织 zhang3 下创建了 文章模板 article-01")
	CreateArticle("article-01", "zhang3", e)

	fmt.Println("// 用户 zhang3 基于 版本风格 chinese-01 和 文章 article-01 创建了 译文 chinses-01+article-01")
	CreateTranslation("chinese-01", "article-01", "zhang3", e)

	fmt.Println("// 用户 zhang3 基于 版本风格 chinese-02 和 文章 article-01 创建了 译文 chinses-02+article-01")
	CreateTranslation("chinese-02", "article-01", "zhang3", e)

	testPermission("zhang3", e)

	fmt.Println("// 创建新用户 li4，等于同时创建了 Org: li4，只是 OrgID 和 UserID 相同")
	CreateOrg("li4", "li4", e)

	testPermission("li4", e)

	fmt.Println("// 将用户 li4 加入到组织 zhang3 里")
	AddOrgMember("zhang3", "li4", e)

	testPermission("li4", e)

	fmt.Println("// 创建新用户 wang5，等于同时创建了 Org: wang5，只是 OrgID 和 UserID 相同")
	CreateOrg("wang5", "wang5", e)

	testPermission("wang5", e)

	fmt.Println("// 将 chinese-01 分享给 wang5，只读 ")
	AddChannelReader("chinese-01", "zhang3", "wang5", e)
	fmt.Println("// 将 article-01 分享给 wang5，只读 ")
	AddArticleReader("article-01", "zhang3", "wang5", e)

	testPermission("wang5", e)

	fmt.Println("// 将 chinese-01 分享给 wang5，可以翻译 ")
	AddChannelTranslator("chinese-01", "zhang3", "wang5", e)

	testPermission("wang5", e)

	fmt.Println("// 创建新用户 zhao6，等于同时创建了 Org: zhao6，只是 OrgID 和 UserID 相同")
	CreateOrg("zhao6", "zhao6", e)

	testPermission("zhao6", e)

	fmt.Println("// 将 译文 chinese-01+article-01 分享给 zhao6，翻译权限")
	AddTranslationTranslator("chinese-01", "article-01", "zhang3", "zhao6", e)
	fmt.Println("// 将 译文 chinese-02+article-01 分享给 zhao6，查看权限")
	AddTranslationReader("chinese-02", "article-01", "zhang3", "zhao6", e)

	testPermission("zhao6", e)

	// Modify the policy.
	// e.AddPolicy(...)
	// e.RemovePolicy(...)

	// Save the policy back to DB.
	e.SavePolicy()
}
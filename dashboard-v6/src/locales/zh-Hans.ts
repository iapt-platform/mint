import forms from "./zh-Hans/forms";
import buttons from "./zh-Hans/buttons";
import nut from "./zh-Hans/nut";
import dict from "./zh-Hans/dict";
import utilities from "./zh-Hans/utilities";
import setting from "./zh-Hans/setting";
import auth from "./zh-Hans/auth";
import course from "./zh-Hans/course";
import message from "./zh-Hans/message";
import label from "./zh-Hans/label";
import error from "./zh-Hans/error";
import pages from "./zh-Hans/pages";
import admin from "./zh-Hans/admin";
import adminStats from "./zh-Hans/admin-stats";
import search from "./zh-Hans/search";

const items = {
  "columns.library.title": "藏经阁",
  "columns.library.home.title": "首页",
  "columns.library.community.title": "社区",
  "columns.library.palicanon.title": "圣典",
  "columns.library.course.title": "课程",
  "columns.library.term.title": "术语百科",
  "columns.library.dict.title": "字典",
  "columns.library.anthology.title": "文集",
  "columns.library.help.title": "帮助",
  "columns.library.more.title": "更多",
  "columns.library.calendar.title": "佛历",
  "columns.library.convertor.title": "巴利字符转换器",
  "columns.library.palihandbook.title": "语法手册",
  "columns.library.statistics.title": "单词统计",
  "columns.library.blog.label": "个人空间",
  "columns.studio.title": "译经楼",
  "columns.studio.palicanon.title": "圣典",
  "columns.studio.recent.title": "最近编辑",
  "columns.studio.channel.title": "版本风格",
  "columns.studio.group.title": "团队",
  "columns.studio.userdict.title": "用户字典",
  "columns.studio.term.title": "术语",
  "columns.studio.course.title": "课程",
  "columns.studio.article.title": "文章",
  "columns.studio.anthology.title": "文集",
  "columns.studio.analysis.title": "分析",
  "columns.studio.collaboration.title": "协作",
  "columns.studio.basic.title": "常用",
  "columns.studio.advance.title": "高级",
  "columns.studio.setting.title": "设置",
  "columns.exp.title": "经验",
  "columns.studio.invite.title": "注册邀请",
  "columns.studio.transfer.title": "转让",
  "columns.studio.attachment.title": "网盘",
  "columns.studio.tag.title": "标签",
  ...buttons,
  ...forms,
  ...nut,
  ...dict,
  ...utilities,
  ...setting,
  ...auth,
  ...course,
  ...message,
  ...label,
  ...error,
  ...pages,
  "channel.links.tab": "关联",
  "channel.links.derived_from": "继承自",
  "channel.links.derived_from.tip": "这个版本是从哪个版本翻译或改编来的，只做记录",
  "channel.links.wbw": "捆绑的逐词",
  "channel.links.wbw.tip": "载入时默认把这个逐词版本当作原文，只能选逐词（wbw）类型",
  "channel.links.fallback": "降级版本",
  "channel.links.fallback.tip": "某个段落没有数据时，按顺序改取这些版本（最多 3 个），只能选与本版本类型相同的",
  "channel.links.term_source": "术语路径",
  "channel.links.term_source.tip": "按顺序从这些版本取术语（最多 10 个），只能选译文（translation）类型",
  "channel.links.placeholder": "搜索版本名称",
  "channel.links.add": "添加",
  "channel.links.readonly": "你没有权限修改这些设置。",
  "channel.links.saved": "已保存",
  "channel.links.empty": "未设置",
  ...admin,
  ...adminStats,
  ...search,
};

export default items;

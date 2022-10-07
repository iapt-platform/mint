import forms from "./forms";
import buttons from "./buttons";
import tables from "./tables";
import nut from "./nut";
import channel from "./channel";
import dict from "./dict";

const items = {
  "flashes.success": "操作成功",
  "columns.library.title": "藏经阁",
  "columns.library.community.title": "社区",
  "columns.library.palicanon.title": "圣典",
  "columns.library.course.title": "课程",
  "columns.library.term.title": "术语百科",
  "columns.library.dict.title": "字典",
  "columns.library.anthology.title": "文集",
  "columns.studio.title": "译经楼",
  "columns.studio.palicanon.title": "圣典",
  "columns.studio.recent.title": "最近编辑",
  "columns.studio.channel.title": "版本风格",
  "columns.studio.group.title": "群组",
  "columns.studio.userdict.title": "用户字典",
  "columns.studio.term.title": "术语",
  "columns.studio.article.title": "文章",
  "columns.studio.anthology.title": "文集",
  "columns.studio.analysis.title": "分析",
  ...buttons,
  ...forms,
  ...tables,
  ...nut,
  ...channel,
  ...dict,
};

export default items;

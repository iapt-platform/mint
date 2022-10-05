import forms from "./forms";
import buttons from "./buttons";
import tables from "./tables";
import nut from "./nut";
import channel from "./channel";

const items = {
  "flashes.success": "操作成功",
  "title.channel": "版本风格",
  ...buttons,
  ...forms,
  ...tables,
  ...nut,
  ...channel,
};

export default items;

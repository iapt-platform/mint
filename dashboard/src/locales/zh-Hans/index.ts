import forms from "./forms";
import buttons from "./buttons";
import tables from "./tables";
import nut from "./nut";

const items = {
  "flashes.success": "操作成功",
  ...buttons,
  ...forms,
  ...tables,
  ...nut,
};

export default items;

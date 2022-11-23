import { Divider } from "antd";
import { SettingFind } from "./default";
import SettingItem from "./SettingItem";

const Widget = () => {
  return (
    <div>
      <Divider>翻译</Divider>
      <SettingItem data={SettingFind("setting.display.original")} />
      <SettingItem data={SettingFind("setting.layout.direction")} />
      <SettingItem data={SettingFind("setting.layout.paragraph")} />
    </div>
  );
};

export default Widget;

import { Divider } from "antd";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";

const SettingArticleWidget = () => {
  return (
    <div>
      <Divider>阅读</Divider>
      <SettingItem data={SettingFind("setting.display.original")} />
      <SettingItem data={SettingFind("setting.layout.direction")} />
      <SettingItem data={SettingFind("setting.layout.paragraph")} />
      <SettingItem data={SettingFind("setting.pali.script.primary")} />
      <SettingItem data={SettingFind("setting.pali.script.secondary")} />
      <Divider>翻译</Divider>

      <Divider>逐词解析</Divider>
      <Divider>字典</Divider>
      <SettingItem data={SettingFind("setting.dict.lang")} />
    </div>
  );
};

export default SettingArticleWidget;

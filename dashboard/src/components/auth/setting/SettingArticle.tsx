import { Divider } from "antd";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";

const SettingArticleWidget = () => {
  const settings = useAppSelector(settingInfo);
  return (
    <div>
      <Divider>阅读</Divider>
      <SettingItem data={SettingFind("setting.display.original", settings)} />
      <SettingItem data={SettingFind("setting.layout.direction", settings)} />
      <SettingItem data={SettingFind("setting.layout.paragraph", settings)} />
      <SettingItem
        data={SettingFind("setting.pali.script.primary", settings)}
      />
      <SettingItem
        data={SettingFind("setting.pali.script.secondary", settings)}
      />
      <Divider>翻译</Divider>

      <Divider>逐词解析</Divider>
      <Divider>Nissaya</Divider>
      <SettingItem
        data={SettingFind("setting.nissaya.layout.read", settings)}
      />
      <SettingItem
        data={SettingFind("setting.nissaya.layout.edit", settings)}
      />
      <Divider>字典</Divider>
      <SettingItem data={SettingFind("setting.dict.lang", settings)} />
    </div>
  );
};

export default SettingArticleWidget;

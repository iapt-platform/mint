import { Divider } from "antd";
import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";
import { useIntl } from "react-intl";

const SettingEditor = () => {
  const settings = useAppSelector(settingInfo);
  const intl = useIntl();
  return (
    <div>
      <Divider>
        {intl.formatMessage({
          id: `buttons.read`,
        })}
      </Divider>
      <SettingItem data={SettingFind("setting.display.original", settings)} />
      <SettingItem data={SettingFind("setting.layout.direction", settings)} />
      <SettingItem data={SettingFind("setting.layout.commentary", settings)} />
      <SettingItem data={SettingFind("setting.layout.root.fixed", settings)} />
      <SettingItem data={SettingFind("setting.layout.paragraph", settings)} />
      <SettingItem
        data={SettingFind("setting.pali.script.primary", settings)}
      />
      <SettingItem
        data={SettingFind("setting.pali.script.secondary", settings)}
      />
      <SettingItem data={SettingFind("setting.term.first.show", settings)} />
      <Divider>
        {intl.formatMessage({
          id: `buttons.translate`,
        })}
      </Divider>
      <SettingItem
        data={SettingFind("setting.translate.layout.direction", settings)}
      />

      <Divider>
        {intl.formatMessage({
          id: `buttons.wbw`,
        })}
      </Divider>
      <SettingItem data={SettingFind("setting.wbw.order", settings)} />
    </div>
  );
};

export default SettingEditor;

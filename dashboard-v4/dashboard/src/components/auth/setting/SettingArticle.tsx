import { Divider } from "antd";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";
import { useIntl } from "react-intl";

const SettingArticleWidget = () => {
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
      <SettingItem data={SettingFind("setting.layout.paragraph", settings)} />
      <SettingItem
        data={SettingFind("setting.pali.script.primary", settings)}
      />
      <SettingItem
        data={SettingFind("setting.pali.script.secondary", settings)}
      />
      <Divider>
        {intl.formatMessage({
          id: `buttons.translate`,
        })}
      </Divider>

      <Divider>
        {intl.formatMessage({
          id: `buttons.wbw`,
        })}
      </Divider>
      <SettingItem data={SettingFind("setting.wbw.order", settings)} />
      <Divider>Nissaya</Divider>
      <SettingItem
        data={SettingFind("setting.nissaya.layout.read", settings)}
      />
      <SettingItem
        data={SettingFind("setting.nissaya.layout.edit", settings)}
      />

      <Divider>
        {intl.formatMessage({
          id: `columns.library.dict.title`,
        })}
      </Divider>
      <SettingItem data={SettingFind("setting.dict.lang", settings)} />
    </div>
  );
};

export default SettingArticleWidget;

import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";

const SettingNissaya = () => {
  const settings = useAppSelector(settingInfo);

  return (
    <div>
      <SettingItem
        data={SettingFind("setting.nissaya.layout.read", settings)}
      />
      <SettingItem
        data={SettingFind("setting.nissaya.layout.edit", settings)}
      />
    </div>
  );
};

export default SettingNissaya;

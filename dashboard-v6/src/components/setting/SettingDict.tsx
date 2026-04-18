import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";

import { SettingFind } from "./default";
import SettingItem from "./SettingItem";

const SettingDict = () => {
  const settings = useAppSelector(settingInfo);
  return (
    <div>
      <SettingItem data={SettingFind("setting.dict.lang", settings)} />
    </div>
  );
};

export default SettingDict;

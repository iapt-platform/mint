import { Typography } from "antd";
import MdView from "../../general/MdView";
import { useMemo } from "react";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { GetUserSetting } from "../../setting/default";
import type { TCodeConvertor } from "../../../types/template";

interface IWidget {
  text?: string;
}
const { Text } = Typography;

const MdOrigin = ({ text }: IWidget) => {
  const settings = useAppSelector(settingInfo);

  /** 派生数据：主巴利编码 */
  const paliCode = useMemo(() => {
    const v = GetUserSetting("setting.pali.script.primary", settings);
    return (v ?? "roman") as TCodeConvertor;
  }, [settings]);
  return (
    <Text className="sent_read_translation" style={{ display: "inline" }}>
      <MdView
        style={{ color: "brown", display: "inline" }}
        html={text}
        wordWidget
        convertor={paliCode}
      />
    </Text>
  );
};

export default MdOrigin;

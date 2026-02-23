import { useMemo } from "react";
import { Typography } from "antd";
import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";
import { getTerm } from "../../reducers/term-vocabulary";
import store from "../../store";
import { lookup as _lookup } from "../../reducers/command";
import type { BaseType } from "antd/lib/typography/Base";
import { my_to_roman, roman_to_my } from "../../utils/code/my";
import { roman_to_si } from "../../utils/code/si";
import { roman_to_thai } from "../../utils/code/thai";
import { roman_to_taitham } from "../../utils/code/tai-tham";
import { GetUserSetting } from "../setting/default";
import type { TCodeConvertor } from "../../types/template";

const { Text } = Typography;

interface IWidget {
  style?: React.CSSProperties;
  text?: string;
  code?: string;
  termToLocal?: boolean;
  lookup?: boolean;
  textType?: BaseType;
}

const PaliTextWidget = ({
  text = "",
  style,
  code = "roman",
  termToLocal = true,
  lookup = false,
  textType,
}: IWidget) => {
  const settings = useAppSelector(settingInfo);
  const terms = useAppSelector(getTerm);

  // 1. 核心逻辑：使用 useMemo 计算最终显示的文本
  const displayedText = useMemo(() => {
    if (!text) return "";

    // 先统一转为罗马文，方便后续处理
    const romanText = code === "my" ? my_to_roman(text) : text;

    // 获取脚本转换配置
    const paliConvertor = GetUserSetting(
      "setting.pali.script.primary",
      settings
    ) as TCodeConvertor;

    let converted: string | undefined;
    switch (paliConvertor) {
      case "roman_to_my":
        converted = roman_to_my(romanText);
        break;
      case "my_to_roman":
        converted = my_to_roman(romanText);
        break;
      case "roman_to_si":
        converted = roman_to_si(romanText);
        break;
      case "roman_to_thai":
        converted = roman_to_thai(romanText);
        break;
      case "roman_to_taitham":
        converted = roman_to_taitham(romanText);
        break;
      default:
        converted = romanText;
    }

    // 2. 如果开启了术语本地化，在转换后的基础上查找（注意：这里取决于你的业务逻辑是查原词还是翻译词）
    if (termToLocal) {
      const lowerCase = romanText?.toLowerCase();
      const found = terms?.find((item) => item.word === lowerCase);
      return found?.meaning || converted;
    }

    return converted;
  }, [text, code, settings, terms, termToLocal]);

  // 3. 事件处理抽离
  const handleLookup = () => {
    const romanText = code === "my" ? my_to_roman(text) : text;
    if (romanText) store.dispatch(_lookup(romanText));
  };

  if (!text) return null;

  if (lookup) {
    return (
      <Text
        style={{ ...style, cursor: "pointer" }}
        type={textType}
        onClick={handleLookup}
      >
        {displayedText}
      </Text>
    );
  }

  return (
    <Text type={textType} style={style}>
      {displayedText}
    </Text>
  );
};

export default PaliTextWidget;

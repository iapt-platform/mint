import { useEffect, useState } from "react";
import { Typography } from "antd";

import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { getTerm } from "../../../reducers/term-vocabulary";

import { GetUserSetting } from "../../auth/setting/default";
import { TCodeConvertor } from "../utilities";
import { roman_to_my, my_to_roman } from "../../code/my";
import { roman_to_si } from "../../code/si";
import { roman_to_thai } from "../../code/thai";
import { roman_to_taitham } from "../../code/tai-tham";
import store from "../../../store";
import { lookup as _lookup } from "../../../reducers/command";
import { BaseType } from "antd/lib/typography/Base";

const { Text } = Typography;

interface IWidget {
  style?: React.CSSProperties;
  text?: string;
  code?: string;
  primary?: boolean;
  termToLocal?: boolean;
  lookup?: boolean;
  textType?: BaseType;
}
const PaliTextWidget = ({
  text,
  style,
  code = "roman",
  primary = true,
  termToLocal = true,
  lookup = false,
  textType,
}: IWidget) => {
  const [paliText, setPaliText] = useState<string>();
  const settings = useAppSelector(settingInfo);
  const terms = useAppSelector(getTerm);

  let romanText: string | undefined;
  if (code === "my") {
    romanText = my_to_roman(text);
  } else {
    romanText = text;
  }
  useEffect(() => {
    if (!termToLocal) {
      return;
    }
    const lowerCase = paliText?.toLowerCase();
    const localName = terms?.find((item) => item.word === lowerCase)?.meaning;
    if (localName) {
      setPaliText(localName);
    }
  }, [paliText, termToLocal, terms]);

  useEffect(() => {
    const _paliCode1 = GetUserSetting("setting.pali.script.primary", settings);
    if (typeof _paliCode1 === "string") {
      const paliConvertor = _paliCode1 as TCodeConvertor;
      //编码转换
      let original = text;
      if (code === "my") {
        original = my_to_roman(text);
      }
      switch (paliConvertor) {
        case "roman_to_my":
          setPaliText(roman_to_my(original));
          break;
        case "my_to_roman":
          setPaliText(my_to_roman(original));
          break;
        case "roman_to_si":
          setPaliText(roman_to_si(original));
          break;
        case "roman_to_thai":
          setPaliText(roman_to_thai(original));
          break;
        case "roman_to_taitham":
          setPaliText(roman_to_taitham(original));
          break;
        default:
          if (code === "my") {
            setPaliText(my_to_roman(text));
          } else {
            setPaliText(text);
          }

          break;
      }
    }
  }, [text, settings, code]);

  const nodePali = text ? (
    <Text type={textType} style={style}>
      {paliText}
    </Text>
  ) : (
    <></>
  );

  if (lookup) {
    return (
      <Typography.Text
        style={{ cursor: "pointer" }}
        type={textType}
        onClick={() => {
          //发送点词查询消息
          if (typeof romanText === "string") {
            store.dispatch(_lookup(romanText));
          }
        }}
      >
        {nodePali}
      </Typography.Text>
    );
  } else {
    return nodePali;
  }
};

export default PaliTextWidget;

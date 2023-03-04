import { useEffect, useRef, useState } from "react";
import { Tooltip, Button, Typography } from "antd";

import { useAppSelector } from "../../hooks";
import {
  onChangeKey,
  onChangeValue,
  settingInfo,
} from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import { TCodeConvertor } from "./utilities";
import { ISentence } from "./SentEdit";
import MdView from "./MdView";
import store from "../../store";
import { push } from "../../reducers/sentence";
const { Text } = Typography;

interface IWidgetSentReadFrame {
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: "row" | "column";
  sentId?: string;
}
const SentReadFrame = ({
  origin,
  translation,
  layout = "column",
  sentId,
}: IWidgetSentReadFrame) => {
  const [paliCode1, setPaliCode1] = useState<TCodeConvertor>("roman");
  const key = useAppSelector(onChangeKey);
  const value = useAppSelector(onChangeValue);
  const settings = useAppSelector(settingInfo);
  const boxOrg = useRef<HTMLDivElement>(null);
  const boxSent = useRef<HTMLDivElement>(null);
  useEffect(() => {
    if (origin && origin.length > 0) {
      store.dispatch(
        push(
          `${origin[0].book}-${origin[0].para}-${origin[0].wordStart}-${origin[0].wordEnd}`
        )
      );
    }
  }, [origin]);
  useEffect(() => {
    const displayOriginal = GetUserSetting(
      "setting.display.original",
      settings
    );
    if (typeof displayOriginal === "boolean") {
      if (boxOrg.current) {
        if (displayOriginal === true) {
          boxOrg.current.style.display = "block";
        } else {
          boxOrg.current.style.display = "none";
        }
      }
    }
    const layoutDirection = GetUserSetting(
      "setting.layout.direction",
      settings
    );
    if (typeof layoutDirection === "string") {
      if (boxSent.current) {
        boxSent.current.style.flexDirection = layoutDirection;
      }
    }

    const _paliCode1 = GetUserSetting("setting.pali.script.primary", settings);
    if (typeof _paliCode1 !== "undefined") {
      setPaliCode1(_paliCode1.toString() as TCodeConvertor);
    }
  }, [key, value, settings]);
  return (
    <Tooltip
      placement="topLeft"
      color="white"
      title={
        <Button type="link" size="small">
          aa
        </Button>
      }
    >
      <div
        style={{ display: "flex", flexDirection: layout, marginBottom: 10 }}
        ref={boxSent}
      >
        <div style={{ flex: "5", color: "#9f3a01" }} ref={boxOrg}>
          {origin?.map((item, id) => {
            return (
              <MdView
                key={id}
                html={item.html}
                wordWidget={true}
                convertor={paliCode1}
              />
            );
          })}
        </div>
        <div style={{ flex: "5" }}>
          {translation?.map((item, id) => {
            if (item.html.indexOf("<hr>") >= 0) console.log(item.html);
            return (
              <Text key={id}>
                <MdView html={item.html} />
              </Text>
            );
          })}
        </div>
      </div>
    </Tooltip>
  );
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
  return (
    <>
      <SentReadFrame {...prop} />
    </>
  );
};

export default Widget;

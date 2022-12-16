import { useEffect, useRef, useState } from "react";
import { Tooltip, Button } from "antd";

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
    switch (key) {
      case "setting.display.original":
        if (boxOrg.current) {
          if (value === true) {
            boxOrg.current.style.display = "block";
          } else {
            boxOrg.current.style.display = "none";
          }
        }
        break;
      case "setting.layout.direction":
        if (boxSent.current) {
          if (typeof value === "string") {
            boxSent.current.style.flexDirection = value;
          }
        }
        break;
      default:
        break;
    }
    const _paliCode1 = GetUserSetting("setting.pali.script1", settings);
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
      <div style={{ display: "flex", flexDirection: layout }} ref={boxSent}>
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
            return <MdView key={id} html={item.html} />;
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

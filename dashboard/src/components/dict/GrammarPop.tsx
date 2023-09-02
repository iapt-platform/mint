import { useState } from "react";
import { Popover, Typography } from "antd";

import { get } from "../../request";
import { get as getLang } from "../../locales";
import { IGuideResponse } from "../api/Guide";
import Marked from "../general/Marked";

const { Link, Paragraph } = Typography;

interface IWidget {
  text: string;
  gid: string;
}
const GrammarPopWidget = ({ text, gid }: IWidget) => {
  const [guide, setGuide] = useState("Loading");
  const grammarPrefix = "guide-grammar-";
  const handleMouseMouseEnter = () => {
    //sessionStorage缓存
    const value = sessionStorage.getItem(grammarPrefix + gid);
    if (value === null) {
      fetchData(gid);
    } else {
      const sGuide: string = value ? value : "";
      setGuide(sGuide);
    }
  };

  function fetchData(key: string) {
    const uiLang = getLang();
    const url = `/v2/grammar-guide/${key}_${uiLang}`;
    get<IGuideResponse>(url).then((json) => {
      if (json.ok) {
        sessionStorage.setItem(grammarPrefix + key, json.data);
        setGuide(json.data);
      }
    });
  }
  return (
    <Popover
      content={
        <Paragraph style={{ maxWidth: 500, minWidth: 300, margin: 0 }}>
          <Marked text={guide} />
        </Paragraph>
      }
      placement="bottom"
    >
      <Link onMouseEnter={handleMouseMouseEnter}>{text}</Link>
    </Popover>
  );
};

interface IWidgetShell {
  props: string;
}
export const GrammarPopShell = ({ props }: IWidgetShell) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  return (
    <>
      <GrammarPopWidget {...prop} />
    </>
  );
};

export default GrammarPopWidget;

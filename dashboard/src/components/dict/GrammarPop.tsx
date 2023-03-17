import { useState } from "react";
import { Popover, Typography } from "antd";
import { ProCard } from "@ant-design/pro-components";

import { get } from "../../request";
import { get as getLang } from "../../locales";
import { IGuideResponse } from "../api/Guide";
import Marked from "../general/Marked";

const { Link } = Typography;

interface IWidget {
  text: string;
  gid: string;
}
const Widget = ({ text, gid }: IWidget) => {
  const [guide, setGuide] = useState("Loading");
  const grammarProfix = "guide-grammar-";
  const handleMouseMouseEnter = () => {
    //sessionStorage缓存
    const value = sessionStorage.getItem(grammarProfix + gid);
    if (value === null) {
      fetchData(gid);
    } else {
      const sGuide: string = value ? value : "";
      setGuide(sGuide);
    }
  };
  const userCard = (
    <>
      <ProCard style={{ maxWidth: 500, minWidth: 300, margin: 0 }}>
        <Marked text={guide} />
      </ProCard>
    </>
  );
  function fetchData(key: string) {
    const uiLang = getLang();
    const url = `/v2/guide/${uiLang}/${key}`;
    get<IGuideResponse>(url).then((json) => {
      if (json.ok) {
        sessionStorage.setItem(grammarProfix + key, json.data);
        setGuide(json.data);
      }
    });
  }
  return (
    <Popover content={userCard} placement="bottom">
      <Link onMouseEnter={handleMouseMouseEnter}>{text}</Link>
    </Popover>
  );
};

export default Widget;

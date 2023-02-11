import { useState } from "react";
import { Popover } from "antd";
import { ProCard } from "@ant-design/pro-components";
import MDEditor from "@uiw/react-md-editor";

import { ApiGetText } from "../../utils";
import { get } from "../../request";
import { IGuideResponse } from "../api/Guide";

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
        <MDEditor.Markdown source={guide} />
      </ProCard>
    </>
  );
  function fetchData(key: string) {
    const url = `/v2/guide/zh-cn/${key}`;
    get<IGuideResponse>(url).then((json) => {
      if (json.ok) {
        sessionStorage.setItem(grammarProfix + key, json.data);
        setGuide(json.data);
      }
    });
  }
  return (
    <Popover content={userCard} placement="bottom">
      <a href="#" onMouseEnter={handleMouseMouseEnter}>
        {text}
      </a>
    </Popover>
  );
};

export default Widget;

import { Affix } from "antd";
import { useEffect, useState } from "react";

import DictComponent from "../dict/DictComponent";

export type TPanelName = "dict" | "channel" | "close";
interface IWidget {
  curr?: TPanelName;
}
const Widget = ({ curr = "close" }: IWidget) => {
  const [dict, setDict] = useState("none");
  const [channel, setChannel] = useState("none");

  useEffect(() => {
    switch (curr) {
      case "dict":
        setDict("block");
        setChannel("none");
        break;
      case "channel":
        setDict("none");
        setChannel("block");
        break;
      default:
        setDict("none");
        setChannel("none");
        break;
    }
  }, [curr]);
  return (
    <Affix offsetTop={44}>
      <div>
        <div
          style={{
            width: 350,
            height: `calc(100vh - 44px)`,
            overflowY: "scroll",
            display: dict,
          }}
        >
          <DictComponent />
        </div>
        <div
          style={{
            width: 350,
            height: `calc(100vh - 44px)`,
            overflowY: "scroll",
            display: channel,
          }}
        >
          channel
        </div>
      </div>
    </Affix>
  );
};

export default Widget;

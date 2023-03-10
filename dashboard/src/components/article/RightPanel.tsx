import { Affix } from "antd";
import { useEffect, useState } from "react";
import { IChannel } from "../channel/Channel";
import ChannelPickerTable from "../channel/ChannelPickerTable";

import DictComponent from "../dict/DictComponent";
import { ArticleType } from "./Article";

export type TPanelName = "dict" | "channel" | "close";
interface IWidget {
  curr?: TPanelName;
  type: ArticleType;
  articleId: string;
  selectedChannelKeys?: string[];
  onChannelSelect?: Function;
  channelReload?: boolean;
}
const Widget = ({
  curr = "close",
  type,
  articleId,
  onChannelSelect,
  selectedChannelKeys,
  channelReload = false,
}: IWidget) => {
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
      <div key="panel">
        <div
          key="DictComponent"
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
          key="ChannelPickerTable"
          style={{
            width: 350,
            height: `calc(100vh - 44px)`,
            overflowY: "scroll",
            display: channel,
          }}
        >
          <ChannelPickerTable
            type={type}
            articleId={articleId}
            selectedKeys={selectedChannelKeys}
            onSelect={(e: IChannel[]) => {
              console.log(e);
              if (typeof onChannelSelect !== "undefined") {
                onChannelSelect(e);
              }
            }}
          />
        </div>
      </div>
    </Affix>
  );
};

export default Widget;

import { Affix, Button, Tabs } from "antd";
import { useEffect, useState } from "react";
import { CloseOutlined } from "@ant-design/icons";

import { IChannel } from "../channel/Channel";
import ChannelPickerTable from "../channel/ChannelPickerTable";
import DictComponent from "../dict/DictComponent";
import { ArticleType } from "./Article";

export type TPanelName = "dict" | "channel" | "close" | "open";
interface IWidget {
  curr?: TPanelName;
  type: ArticleType;
  articleId: string;
  selectedChannelKeys?: string[];
  onChannelSelect?: Function;
  onClose?: Function;
}
const RightPanelWidget = ({
  curr = "close",
  type,
  articleId,
  onChannelSelect,
  selectedChannelKeys,
  onClose,
}: IWidget) => {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    switch (curr) {
      case "open":
        setOpen(true);
        break;
      case "dict":
        setOpen(true);
        break;
      case "channel":
        setOpen(true);
        break;
      case "close":
        setOpen(false);
        break;
      default:
        setOpen(false);
        break;
    }
  }, [curr]);
  return (
    <Affix offsetTop={44}>
      <div
        key="panel"
        style={{
          width: 350,
          height: `calc(100vh - 44px)`,
          overflowY: "scroll",
          display: open ? "block" : "none",
        }}
      >
        <Tabs
          size="small"
          defaultActiveKey={curr}
          tabBarExtraContent={{
            right: (
              <Button
                type="text"
                size="small"
                icon={<CloseOutlined />}
                onClick={() => {
                  if (typeof onClose !== "undefined") {
                    onClose();
                  }
                }}
              />
            ),
          }}
          items={[
            {
              label: `字典`,
              key: "dict",
              children: <DictComponent />,
            },
            {
              label: `channel`,
              key: "channel",
              children: (
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
              ),
            },
          ]}
        />
      </div>
    </Affix>
  );
};

export default RightPanelWidget;

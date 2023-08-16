import { Affix, Button, Tabs } from "antd";
import { useEffect, useState } from "react";
import { CloseOutlined } from "@ant-design/icons";

import { IChannel } from "../channel/Channel";
import ChannelPickerTable from "../channel/ChannelPickerTable";
import DictComponent from "../dict/DictComponent";
import { ArticleType } from "./Article";
import { useAppSelector } from "../../hooks";
import { openPanel, rightPanel } from "../../reducers/right-panel";
import store from "../../store";

export type TPanelName = "dict" | "channel" | "close" | "open";
interface IWidget {
  curr?: TPanelName;
  type: ArticleType;
  articleId: string;
  selectedChannelsId?: string[];
  onChannelSelect?: Function;
  onClose?: Function;
  onTabChange?: Function;
}
const RightPanelWidget = ({
  curr = "close",
  type,
  articleId,
  onChannelSelect,
  selectedChannelsId,
  onClose,
  onTabChange,
}: IWidget) => {
  const [open, setOpen] = useState(false);
  const [activeTab, setActiveTab] = useState<string>("dict");

  const _openPanel = useAppSelector(rightPanel);

  useEffect(() => {
    console.log("panel", _openPanel);
    if (typeof _openPanel !== "undefined") {
      if (typeof onTabChange !== "undefined") {
        onTabChange(_openPanel);
      }
      store.dispatch(openPanel(undefined));
    }
  }, [_openPanel]);

  useEffect(() => {
    switch (curr) {
      case "open":
        setOpen(true);
        break;
      case "dict":
        setOpen(true);
        setActiveTab(curr);
        break;
      case "channel":
        setOpen(true);
        setActiveTab(curr);
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
          activeKey={activeTab}
          onChange={(activeKey: string) => setActiveTab(activeKey)}
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
                  selectedKeys={selectedChannelsId}
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

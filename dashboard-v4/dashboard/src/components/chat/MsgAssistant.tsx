import { Button, Dropdown, message, Space, Tooltip, Typography } from "antd";
import { Message } from "./AiChat";

import {
  CopyOutlined,
  ReloadOutlined,
  LeftOutlined,
  RightOutlined,
} from "@ant-design/icons";
import { IAiModel } from "../api/ai";
import { useState } from "react";
import { MenuProps } from "antd/es/menu";
import Marked from "../general/Marked";

const { Text } = Typography;

interface IWidget {
  msg?: Message;
  models?: IAiModel[];
  onRefresh?: (modelIndex: number) => void;
}

const MsgAssistant = ({ msg, models, onRefresh }: IWidget) => {
  const [currentVersion, setCurrentVersion] = useState(0);

  const switchMessageVersion = (direction: "prev" | "next"): void => {
    if (msg && msg.versions) {
      const maxIndex = msg.versions.length - 1;

      let newIndex = currentVersion;
      if (direction === "prev" && currentVersion > 0) {
        newIndex = currentVersion - 1;
      } else if (direction === "next" && currentVersion < maxIndex) {
        newIndex = currentVersion + 1;
      }
      setCurrentVersion(newIndex);
    }
  };

  const refreshMenu: MenuProps = {
    onClick: ({ key }) => {
      if (key === "refresh") {
        onRefresh && onRefresh(0);
      }
    },
    items: [
      {
        key: "refresh",
        label: "重新生成",
      },
      {
        type: "divider",
      },
      {
        key: "model-submenu",
        label: "选择模型重新生成",
        children: models?.map((model, id) => ({
          key: model.uid,
          label: model.name,
          onClick: () => {
            onRefresh && onRefresh(id);
          },
        })),
      },
    ],
  };
  return (
    <div
      style={{
        display: "flex",
        justifyContent: "flex-start",
      }}
    >
      <div
        style={{
          maxWidth: "90%",
          backgroundColor: "#ffffff",
          color: "black",
          borderRadius: "8px",
          padding: "16px",
          border: "none",
          boxShadow: "0 1px 2px rgba(0, 0, 0, 0.03)",
          textAlign: "left",
        }}
      >
        <div
          style={{
            fontSize: "14px",
            fontWeight: 500,
            marginBottom: "4px",
          }}
        >
          {msg?.model
            ? models?.find((m) => m.uid === msg.model)?.name
            : "AI助手"}
        </div>
        <div>
          <Marked text={msg?.content} />
        </div>
        <div>
          <Space>
            {msg?.versions && msg.versions.length > 1 && (
              <div style={{ marginBottom: "8px" }}>
                <Space size="small">
                  <Button
                    size="small"
                    type="text"
                    icon={<LeftOutlined />}
                    disabled={msg.currentVersionIndex === 0}
                    onClick={() => switchMessageVersion("prev")}
                  />
                  <Text
                    style={{
                      fontSize: "12px",
                      color:
                        msg.type === "user" ? "rgba(255,255,255,0.7)" : "#666",
                    }}
                  >
                    {(msg.currentVersionIndex || 0) + 1}/{msg.versions.length}
                  </Text>
                  <Button
                    size="small"
                    type="text"
                    icon={<RightOutlined />}
                    disabled={
                      msg.currentVersionIndex === msg.versions.length - 1
                    }
                    onClick={() => switchMessageVersion("next")}
                  />
                </Space>
              </div>
            )}
            <div>
              <Space size="small">
                <Tooltip title="复制">
                  <Button
                    size="small"
                    type="text"
                    icon={<CopyOutlined />}
                    onClick={() => {
                      msg &&
                        navigator.clipboard
                          .writeText(msg.content)
                          .then((value) => message.success("已复制到剪贴板"))
                          .catch((reason: any) => {
                            console.error("复制失败:", reason);
                            message.error("复制失败");
                          });
                    }}
                  />
                </Tooltip>
                <Dropdown menu={refreshMenu} trigger={["hover"]}>
                  <Button size="small" type="text" icon={<ReloadOutlined />} />
                </Dropdown>
              </Space>
            </div>
          </Space>
        </div>
      </div>
    </div>
  );
};

export default MsgAssistant;

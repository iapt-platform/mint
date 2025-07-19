import { Button, Dropdown, message, Space, Tooltip, Typography } from "antd";
import { Message } from "./AiChat";

import {
  CopyOutlined,
  ReloadOutlined,
  LeftOutlined,
  RightOutlined,
} from "@ant-design/icons";
import { IAiModel } from "../api/ai";
import { useEffect, useState } from "react";
import { MenuProps } from "antd/es/menu";
import Marked from "../general/Marked";
import MsgContainer from "./MsgContainer";

const { Text } = Typography;

interface IWidget {
  msg?: Message;
  models?: IAiModel[];
  onRefresh?: (modelId: string) => void;
}

const MsgAssistant = ({ msg, models, onRefresh }: IWidget) => {
  const [currentVersion, setCurrentVersion] = useState(0);

  useEffect(() => {
    if (msg) {
      setCurrentVersion(msg?.versions.length - 1);
    }
  }, [msg]);

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
      if (key === "refresh" && msg) {
        onRefresh && onRefresh(msg.versions[currentVersion].model);
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
            onRefresh && onRefresh(model.uid);
          },
        })),
      },
    ],
  };
  return (
    <MsgContainer>
      <div
        style={{
          fontSize: "14px",
          fontWeight: 500,
          marginBottom: "4px",
        }}
      >
        {msg?.versions[currentVersion].model
          ? models?.find((m) => m.uid === msg.versions[currentVersion].model)
              ?.name
          : "AI助手"}
      </div>
      <div>
        <Marked text={msg?.versions[currentVersion].content} />
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
                  disabled={currentVersion === 0}
                  onClick={() => switchMessageVersion("prev")}
                />
                <Text
                  style={{
                    fontSize: "12px",
                    color:
                      msg.type === "user" ? "rgba(255,255,255,0.7)" : "#666",
                  }}
                >
                  {(currentVersion || 0) + 1}/{msg.versions.length}
                </Text>
                <Button
                  size="small"
                  type="text"
                  icon={<RightOutlined />}
                  disabled={currentVersion === msg.versions.length - 1}
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
                        .writeText(msg.versions[currentVersion].content)
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
    </MsgContainer>
  );
};

export default MsgAssistant;

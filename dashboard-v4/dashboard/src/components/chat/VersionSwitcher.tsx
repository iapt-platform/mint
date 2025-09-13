import React from "react";
import { Button, Space, Tooltip } from "antd";
import { LeftOutlined, RightOutlined } from "@ant-design/icons";
import { VersionSwitcherProps } from "../../types/chat";

export function VersionSwitcher({
  versions,
  currentVersion,
  onSwitch,
}: VersionSwitcherProps) {
  if (versions.length <= 1) return null;

  const canGoPrev = currentVersion > 0;
  const canGoNext = currentVersion < versions.length - 1;

  const currentVersionInfo = versions[currentVersion];

  return (
    <div className="version-switcher">
      <Space align="center">
        <Button
          size="small"
          type="text"
          icon={<LeftOutlined />}
          disabled={!canGoPrev}
          onClick={() => canGoPrev && onSwitch(currentVersion - 1)}
        />

        <Tooltip
          title={
            <div>
              <div>
                版本 {currentVersion + 1} / {versions.length}
              </div>
              <div>模型: {currentVersionInfo.model_id || "未知"}</div>
            </div>
          }
        >
          <span className="version-info">
            {currentVersion + 1} / {versions.length}
          </span>
        </Tooltip>

        <Button
          size="small"
          type="text"
          icon={<RightOutlined />}
          disabled={!canGoNext}
          onClick={() => canGoNext && onSwitch(currentVersion + 1)}
        />
      </Space>
    </div>
  );
}

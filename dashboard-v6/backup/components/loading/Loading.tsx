// src/components/Loading.tsx
import React from "react";
import { Spin } from "antd";
import "./Loading.css";

interface LoadingProps {
  tip?: string;
  fullscreen?: boolean;
}

const Loading: React.FC<LoadingProps> = ({
  tip = "加载中...",
  fullscreen = true,
}) => {
  if (fullscreen) {
    return (
      <div className="page-loading">
        <Spin size="large" tip={tip} />
      </div>
    );
  }

  return <Spin tip={tip} />;
};

export default Loading;

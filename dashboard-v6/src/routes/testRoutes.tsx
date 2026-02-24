import { lazy } from "react";
import type { ComponentType } from "react";

const TestVideoPlayerTest = lazy(
  () => import("../components/video/VideoPlayerTest")
);

// 你可以继续添加更多测试组件
// const TestButtonDemo = lazy(() => import("../components/button/ButtonDemo"));

export interface TestRouteObject {
  path: string;
  label: string;
  icon?: string; // 可选图标（emoji 或 icon key）
  Component?: ComponentType;
  children?: TestRouteObject[];
}

export const testRoutes: TestRouteObject[] = [
  {
    path: "VideoPlayer",
    label: "视频播放器",
    Component: TestVideoPlayerTest,
  },
  // 示例：嵌套结构
  // {
  //   path: "button",
  //   label: "按钮",
  //   children: [
  //     {
  //       path: "basic",
  //       label: "基础按钮",
  //       Component: TestButtonDemo,
  //     },
  //   ],
  // },
];

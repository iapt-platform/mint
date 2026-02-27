import { lazy } from "react";
import type { ComponentType } from "react";

const TestVideoPlayerTest = lazy(
  () => import("../components/video/VideoPlayerTest")
);
const SentSimTest = lazy(
  () => import("../components/sentence-editor/SentSimTest")
);
const SentEditInnerDemo = lazy(
  () => import("../components/sentence-editor/SentEditInnerDemo")
);

const TermTest = lazy(() => import("../components/term/TermTest"));

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
  {
    path: "SimSentence",
    label: "相似句",
    Component: SentSimTest,
  },
  {
    path: "SentEditInnerDemo",
    label: "SentEditInnerDemo",
    Component: SentEditInnerDemo,
  },
  {
    path: "TermTest",
    label: "TermTest",
    Component: TermTest,
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

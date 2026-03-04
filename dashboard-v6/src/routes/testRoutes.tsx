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
const EditableTreeTest = lazy(
  () => import("../components/article/components/EditableTreeTest")
);

const TermTest = lazy(() => import("../components/term/TermTest"));
const TypePaliTest = lazy(() => import("../components/article/TypePaliTest"));
const SplitLayoutTest = lazy(
  () => import("../components/general/SplitLayout/SplitLayoutTest")
);

const ArticleReader = lazy(
  () => import("../components/article/ArticleReaderTest")
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
    path: "EditableTreeTest",
    label: "EditableTreeTest",
    Component: EditableTreeTest,
  },
  {
    path: "SplitLayoutTest",
    label: "SplitLayoutTest",
    Component: SplitLayoutTest,
  },
  {
    path: "editor",
    label: "Editor",
    children: [
      {
        path: "TermTest",
        label: "TermTest",
        Component: TermTest,
      },
      {
        path: "TypePaliTest",
        label: "TypePaliTest",
        Component: TypePaliTest,
      },
      {
        path: "ArticleReader",
        label: "ArticleReader",
        Component: ArticleReader,
      },
    ],
  },
];

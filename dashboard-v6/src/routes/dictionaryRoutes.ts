// src/routes/dictionaryRoutes.ts
// 「字典」「语法手册」的独立页面，挂在根下（/dictionary、/grammar-book），不在 workspace 布局里：
// 主导航「工具」里打开的是弹窗，弹窗「在新标签页打开」跳到这里。
// 注意 /workspace/dict 是「用户字典」，不要混淆。
import { lazy } from "react";
import type { RouteObject, UIMatch } from "react-router";

const Dictionary = lazy(() => import("../pages/dictionary"));
const GrammarBook = lazy(() => import("../pages/grammar-book"));

const dictionaryRoutes: RouteObject[] = [
  {
    path: "dictionary",
    handle: { id: "dictionary", crumb: "dictionary" },
    children: [
      { index: true, Component: Dictionary },
      // 查词结果可重入：/dictionary/dhamma
      {
        path: ":word",
        Component: Dictionary,
        handle: {
          crumb: (match: UIMatch) => match.params.word ?? "",
        },
      },
    ],
  },
  {
    path: "grammar-book",
    Component: GrammarBook,
    handle: { id: "grammar-book", crumb: "grammar-book" },
  },
];

export default dictionaryRoutes;

// src/routes/dictionaryRoutes.ts
// 编辑器右边栏的「字典」「语法手册」的独立页面。
// 注意 /workspace/dict 是「用户字典」，不要混淆。
import { lazy } from "react";
import type { RouteObject, UIMatch } from "react-router";

const WorkspaceDictionary = lazy(() => import("../pages/workspace/dictionary"));
const WorkspaceGrammarBook = lazy(
  () => import("../pages/workspace/grammar-book"),
);

const dictionaryRoutes: RouteObject[] = [
  {
    path: "dictionary",
    handle: { id: "workspace.dictionary", crumb: "dictionary" },
    children: [
      { index: true, Component: WorkspaceDictionary },
      // 查词结果可重入：/workspace/dictionary/dhamma
      {
        path: ":word",
        Component: WorkspaceDictionary,
        handle: {
          crumb: (match: UIMatch) => match.params.word ?? "",
        },
      },
    ],
  },
  {
    path: "grammar-book",
    Component: WorkspaceGrammarBook,
    handle: { id: "workspace.grammar-book", crumb: "grammar-book" },
  },
];

export default dictionaryRoutes;

// src/routes/channelRoutes.ts
import { createElement, lazy } from "react";
import type { RouteObject } from "react-router";
import { channelLoader } from "../api/channel";
import RouteError from "../components/general/RouteError";

const WorkspaceChannel = lazy(() => import("../pages/workspace/channel/list"));
const WorkspaceChannelShow = lazy(
  () => import("../pages/workspace/channel/show"),
);
const WorkspaceChannelSetting = lazy(
  () => import("../pages/workspace/channel/setting"),
);

const channelRoutes: RouteObject[] = [
  {
    path: "channel",
    handle: { id: "workspace.channel", crumb: "channel" },
    children: [
      {
        index: true,
        Component: WorkspaceChannel,
      },
      {
        path: ":channelId",
        loader: channelLoader,
        // channel 不存在（404）或看不见（v3 回 403，loader 统一抛 404）时给友好提示
        errorElement: createElement(RouteError, {
          backTo: "/workspace/channel",
        }),
        handle: {
          crumb: (match: { data?: { name?: string } }) =>
            match.data?.name ?? "",
        },
        children: [
          {
            index: true,
            Component: WorkspaceChannelShow,
          },
          {
            path: "setting",
            Component: WorkspaceChannelSetting,
            handle: { id: "workspace.channel.setting", crumb: "setting" },
          },
          // 设置页的页签（basic / webhooks）与 webhook 详情（new 或 webhook id）
          {
            path: "setting/:type",
            Component: WorkspaceChannelSetting,
            handle: { id: "workspace.channel.setting", crumb: "setting" },
          },
          {
            path: "setting/:type/:id",
            Component: WorkspaceChannelSetting,
            handle: { id: "workspace.channel.setting", crumb: "setting" },
          },
        ],
      },
    ],
  },
];

export default channelRoutes;

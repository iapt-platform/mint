// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { channelLoader } from "../api/channel";

const WorkspaceChannel = lazy(
  () => import("../pages/workspace/channel/list")
);
const WorkspaceChannelShow = lazy(
  () => import("../pages/workspace/channel/show")
);
const WorkspaceChannelSetting = lazy(
  () => import("../pages/workspace/channel/setting")
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
        handle: {
          crumb: (match: { data: { name: string } }) => match.data.name,
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
        ],
      },
    ],
  },
];

export default channelRoutes;

import { IPlugin } from "..";

export const plugin: IPlugin = {
    routes: [
        {
            path: "/demo/topics/new",
            component: () => import("./topics/new"),
        },
        {
            path: "/demo/topics/edit",
            component: () => import("./topics/edit"),
        },
        {
            path: "/demo/topics",
            component: () => import("./topics/index"),
        },
        {
            path: "/demo/posts/new",
            component: () => import("./posts/new"),
        },
        {
            path: "/demo/posts/edit",
            component: () => import("./posts/edit"),
        },
        {
            path: "/demo/posts",
            component: () => import("./posts/index"),
        },
    ],
};

export default plugin;

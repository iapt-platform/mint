import nut from "./nut";
import demo from "./demo";

export interface IRoute {
    path: string;
    component: any;
}

export interface IPlugin {
    routes: IRoute[];
}

const plugin: IPlugin = {
    routes: [...demo.routes, ...nut.routes],
};
export default plugin;

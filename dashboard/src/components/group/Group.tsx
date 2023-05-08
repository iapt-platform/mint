import { Space } from "antd";

export interface IGroup {
  id: string;
  name: string;
}
interface IWidget {
  group?: IGroup;
}
const Widget = ({ group }: IWidget) => {
  return <Space>{group?.name}</Space>;
};

export default Widget;

import { Space } from "antd";
import type { IGroup } from "../../api/Group";

interface IWidget {
  group?: IGroup;
}
const GroupWidget = ({ group }: IWidget) => {
  return <Space>{group?.name}</Space>;
};

export default GroupWidget;

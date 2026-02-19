import { Space } from "antd";

interface IWidget {
  group?: IGroup;
}
const GroupWidget = ({ group }: IWidget) => {
  return <Space>{group?.name}</Space>;
};

export default GroupWidget;

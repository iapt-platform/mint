import { Popover } from "antd";
import { WarningOutlined } from "@ant-design/icons";

interface IWidget {
  children?: React.ReactNode;
}
const ParserErrorWidget = ({ children }: IWidget) => {
  return (
    <Popover content={children} placement="bottom">
      <WarningOutlined style={{ color: "red" }} />
    </Popover>
  );
};

export default ParserErrorWidget;

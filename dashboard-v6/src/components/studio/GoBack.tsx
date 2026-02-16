import { Button, Space } from "antd";
import { Link } from "react-router-dom";
import { ArrowLeftOutlined } from "@ant-design/icons";

interface IWidgetGoBack {
  to: string;
  title?: string;
}
const GoBackWidget = (prop: IWidgetGoBack) => {
  return (
    <Space>
      <Link to={prop.to}>
        <Button shape="circle" icon={<ArrowLeftOutlined />} />
      </Link>
      <span>{prop.title}</span>
    </Space>
  );
};

export default GoBackWidget;

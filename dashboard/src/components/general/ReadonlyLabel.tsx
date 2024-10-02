import { Space, Tooltip, Typography } from "antd";
import { QuestionCircleOutlined } from "@ant-design/icons";
const { Text } = Typography;
const ReadonlyLabelWidget = () => {
  return (
    <Tooltip placement="top" title={"您可能没有登录，或者没有修改权限。"}>
      <Text type="warning">
        <Space>
          {"只读"}
          <QuestionCircleOutlined />
        </Space>
      </Text>
    </Tooltip>
  );
};

export default ReadonlyLabelWidget;

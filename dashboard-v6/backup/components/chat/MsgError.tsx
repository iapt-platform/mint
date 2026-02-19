import { Alert, Button } from "antd";
import { ReloadOutlined } from "@ant-design/icons";
interface IWidget {
  message?: string;
  onRefresh?: () => void;
}
const MsgError = ({ message, onRefresh }: IWidget) => {
  return (
    <Alert
      type="error"
      closable={false}
      showIcon
      message={message}
      action={
        <Button type="text" icon={<ReloadOutlined />} onClick={onRefresh}>
          刷新
        </Button>
      }
    />
  );
};

export default MsgError;

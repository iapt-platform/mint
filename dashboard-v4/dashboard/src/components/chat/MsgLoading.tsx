import MsgContainer from "./MsgContainer";
import { IAiModel } from "../api/ai";
import User from "../auth/User";
import { Space } from "antd";

interface IWidget {
  model?: IAiModel;
}
const MsgLoading = ({ model }: IWidget) => {
  return (
    <MsgContainer>
      <Space>
        <User {...model?.user} />
        正在思考……
      </Space>
    </MsgContainer>
  );
};

export default MsgLoading;

import { Typography } from "antd";
import { lookup } from "../../reducers/command";
import store from "../../store";

interface IWidget {
  search?: string;
  children?: React.ReactNode;
}
const Widget = ({ search, children }: IWidget) => {
  return (
    <Typography.Text
      style={{ cursor: "pointer" }}
      onClick={() => {
        //发送点词查询消息
        if (typeof search === "string") {
          store.dispatch(lookup(search));
        }
      }}
    >
      {children}
    </Typography.Text>
  );
};

export default Widget;

import { Badge } from "antd";

interface IWidget {
  count: number;
  active?: boolean;
}
const Widget = ({ count, active = false }: IWidget) => {
  return (
    <Badge
      count={count}
      style={{
        marginBlockStart: -2,
        marginInlineStart: 4,
        color: active ? "#1890FF" : "#999",
        backgroundColor: active ? "#E6F7FF" : "#eee",
      }}
    />
  );
};

export default Widget;

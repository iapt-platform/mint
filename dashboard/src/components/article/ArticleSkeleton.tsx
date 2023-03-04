import { Divider, Skeleton } from "antd";

const Widget = () => {
  return (
    <div style={{ paddingTop: "1em" }}>
      <Skeleton title={{ width: 200 }} paragraph={{ rows: 1 }} active />
      <Divider />
      <Skeleton title={{ width: 200 }} paragraph={{ rows: 10 }} active />
    </div>
  );
};

export default Widget;

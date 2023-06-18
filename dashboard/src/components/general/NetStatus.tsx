import { Button } from "antd";
import { CloudOutlined } from "@ant-design/icons";
import { useState } from "react";

interface IWidget {
  style?: React.CSSProperties;
}
const Widget = ({ style }: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [label, setLabel] = useState("online");
  return (
    <>
      <Button
        style={style}
        type="text"
        loading={loading}
        icon={<CloudOutlined />}
      >
        {label}
      </Button>
    </>
  );
};

export default Widget;

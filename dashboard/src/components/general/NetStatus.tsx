import { Button } from "antd";
import { CloudOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { netStatus } from "../../reducers/net-status";

export type ENetStatus = "loading" | "success" | "fail";

interface IWidget {
  style?: React.CSSProperties;
}
const Widget = ({ style }: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [label, setLabel] = useState("online");

  const _netStatus = useAppSelector(netStatus);

  useEffect(() => {
    console.log("net status", _netStatus);
    switch (_netStatus?.status) {
      case "loading":
        setLoading(true);
        break;
      case "success":
        setLoading(false);
        break;
      case "fail":
        setLoading(false);
        break;
      default:
        break;
    }
    if (_netStatus?.message) {
      setLabel(_netStatus?.message);
    }
  }, [_netStatus]);
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
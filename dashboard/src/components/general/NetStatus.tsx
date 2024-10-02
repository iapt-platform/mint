import { Button } from "antd";
import { CloudOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { netStatus } from "../../reducers/net-status";

export type ENetStatus = "loading" | "success" | "fail";

interface IWidget {
  style?: React.CSSProperties;
}
const NetStatusWidget = ({ style }: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [label, setLabel] = useState("online");

  const _netStatus = useAppSelector(netStatus);

  useEffect(() => {
    // 监听网络连接状态变化
    const onOnline = () => console.info("网络连接已恢复");
    const onOffline = () => console.info("网络连接已中断");

    window.addEventListener("online", onOnline);
    window.addEventListener("offline", onOffline);

    return () => {
      window.removeEventListener("online", onOnline);
      window.removeEventListener("offline", onOffline);
    };
  }, []);

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

export default NetStatusWidget;

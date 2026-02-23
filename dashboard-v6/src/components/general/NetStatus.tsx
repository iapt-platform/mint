import { Button } from "antd";
import { CloudOutlined } from "@ant-design/icons";
import { useEffect } from "react";
import { useAppSelector } from "../../hooks";
import { netStatus } from "../../reducers/net-status";

interface IWidget {
  style?: React.CSSProperties;
}
const NetStatusWidget = ({ style }: IWidget) => {
  const mNetStatus = useAppSelector(netStatus);

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

  let loading = false;
  console.log("net status", mNetStatus);
  switch (mNetStatus?.status) {
    case "loading":
      loading = true;
      break;
    case "success":
      loading = false;
      break;
    case "fail":
      loading = false;
      break;
    default:
      break;
  }
  let label = "online";
  if (mNetStatus?.message) {
    label = mNetStatus?.message;
  }

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

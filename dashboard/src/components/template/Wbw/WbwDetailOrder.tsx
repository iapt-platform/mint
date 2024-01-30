import { Button, Tooltip } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { GetUserSetting } from "../../auth/setting/default";

interface IWidget {
  sn: number;
  curr: number;
  onChange?: Function;
}

const WbwDetailOrderWidget = ({ sn, curr, onChange }: IWidget) => {
  const [show, setShow] = useState(true);
  const [enable, setEnable] = useState(false);
  const settings = useAppSelector(settingInfo);

  useEffect(() => {
    const showOrder = GetUserSetting("setting.wbw.order", settings);
    if (typeof showOrder === "boolean") {
      setEnable(showOrder);
    }
  }, [settings]);

  useEffect(() => {
    setShow(sn === curr);
  }, [curr, sn]);
  return enable ? (
    <Tooltip
      open={show}
      placement="right"
      title={
        <Button
          type="link"
          size="small"
          onClick={() => {
            if (typeof onChange !== "undefined") {
              onChange();
            }
          }}
        >
          {curr === 5 ? "完成" : "下一步"}
        </Button>
      }
    >
      <span style={{ display: "inline-box", width: 1, height: 30 }}></span>
    </Tooltip>
  ) : (
    <></>
  );
};

export default WbwDetailOrderWidget;

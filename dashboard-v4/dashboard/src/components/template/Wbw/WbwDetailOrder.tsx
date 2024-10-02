import { Button, Tooltip } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { GetUserSetting } from "../../auth/setting/default";

interface IWidget {
  sn: number;
  curr: number;
  visible?: boolean;
  onChange?: Function;
}

const WbwDetailOrderWidget = ({
  sn,
  curr,
  visible = false,
  onChange,
}: IWidget) => {
  const [show, setShow] = useState(true);
  const settings = useAppSelector(settingInfo);

  const showOrder = GetUserSetting("setting.wbw.order", settings);
  const enable = visible && showOrder;

  useEffect(() => {
    setShow(sn === curr);
  }, [curr, sn]);

  return enable ? (
    <Tooltip
      open={show}
      placement="right"
      getTooltipContainer={(node: HTMLElement) =>
        document.getElementsByClassName("wbw_detail")[0] as HTMLElement
      }
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

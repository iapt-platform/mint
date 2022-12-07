import { useState } from "react";
import { Popover } from "antd";
import { TagTwoTone, InfoCircleOutlined } from "@ant-design/icons";

import WbwDetail from "./WbwDetail";
import { IWbw } from "./WbwWord";
import { bookMarkColor } from "./WbwDetailBookMark";
import "./wbw.css";

interface IWidget {
  data: IWbw;
  onSave?: Function;
}
const Widget = ({ data, onSave }: IWidget) => {
  const [open, setOpen] = useState(false);
  const [paliColor, setPaliColor] = useState("unset");
  const wbwDetail = (
    <WbwDetail
      data={data}
      onClose={() => {
        setPaliColor("unset");
        setOpen(false);
      }}
      onSave={(e: IWbw) => {
        if (typeof onSave !== "undefined") {
          onSave(e);
          setOpen(false);
          setPaliColor("unset");
        }
      }}
    />
  );
  const handleClickChange = (open: boolean) => {
    setOpen(open);
    if (open) {
      setPaliColor("lightblue");
    } else {
      setPaliColor("unset");
    }
  };
  const noteIcon = data.note ? (
    <Popover content={data.note.value} placement="bottom">
      <InfoCircleOutlined style={{ color: "blue" }} />
    </Popover>
  ) : (
    <></>
  );
  const color = data.bookMarkColor
    ? bookMarkColor[data.bookMarkColor.value]
    : "white";

  const bookMarkIcon = data.bookMarkText ? (
    <Popover content={data.bookMarkText.value} placement="bottom">
      <TagTwoTone twoToneColor={color} />
    </Popover>
  ) : (
    <></>
  );
  return (
    <div className="pali_shell">
      <Popover
        content={wbwDetail}
        placement="bottom"
        trigger="click"
        open={open}
        onOpenChange={handleClickChange}
      >
        <span
          className="pali"
          style={{ backgroundColor: paliColor, padding: 4, borderRadius: 5 }}
        >
          {data.word.value}
        </span>
      </Popover>
      {noteIcon}
      {bookMarkIcon}
    </div>
  );
};

export default Widget;

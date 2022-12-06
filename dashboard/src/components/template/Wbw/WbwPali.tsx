import { useState } from "react";
import { Popover } from "antd";
import { TagFilled, InfoCircleOutlined } from "@ant-design/icons";

import WbwDetail from "./WbwDetail";
import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
  onSave?: Function;
}
const Widget = ({ data, onChange, onSave }: IWidget) => {
  const [open, setOpen] = useState(false);
  const [paliColor, setPaliColor] = useState("unset");
  const wbwDetail = (
    <WbwDetail
      data={data}
      onClose={() => {
        setPaliColor("unset");
        setOpen(false);
      }}
      onChange={(e: IWbw) => {
        if (typeof onChange !== "undefined") {
          onChange(e);
        }
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
  const bookMarkIcon = data.bookMarkText ? (
    <Popover content={data.bookMarkText.value} placement="bottom">
      <TagFilled style={{ color: data.bookMarkColor?.value }} />
    </Popover>
  ) : (
    <></>
  );
  return (
    <div>
      <Popover
        content={wbwDetail}
        placement="bottom"
        trigger="click"
        open={open}
        onOpenChange={handleClickChange}
      >
        <span
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

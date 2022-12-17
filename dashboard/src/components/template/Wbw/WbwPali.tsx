import { useState } from "react";
import { Popover, Typography } from "antd";
import { TagTwoTone, InfoCircleOutlined } from "@ant-design/icons";

import WbwDetail from "./WbwDetail";
import { IWbw } from "./WbwWord";
import { bookMarkColor } from "./WbwDetailBookMark";
import "./wbw.css";
import { PaliReal } from "../../../utils";
import WbwVideoButton from "./WbwVideoButton";
import { IVideo } from "./WbwVideoButton";
const { Paragraph } = Typography;
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

  //生成视频播放按钮
  const videoList = data.attachments?.filter((word) => word.type === "video");
  const videoIcon = videoList ? (
    <WbwVideoButton
      video={videoList?.map((item) => {
        return {
          url: item.url ? item.url : "",
          title: item.name,
        };
      })}
    />
  ) : undefined;

  if (typeof data.attachments !== "undefined") {
  }

  const bookMarkIcon = data.bookMarkText ? (
    <Popover
      content={<Paragraph copyable>{data.bookMarkText.value}</Paragraph>}
      placement="bottom"
    >
      <TagTwoTone twoToneColor={color} />
    </Popover>
  ) : (
    <></>
  );
  const classPali = data.style?.value === "note" ? "wbw_note" : "pali";
  let padding: string;
  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    padding = "4px";
  } else {
    padding = "4px 0";
  }
  const paliWord = (
    <span
      className={classPali}
      style={{
        backgroundColor: paliColor,
        padding: padding,
        borderRadius: 5,
      }}
    >
      {data.word.value}
    </span>
  );

  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    //非标点符号
    return (
      <div className="pali_shell">
        <Popover
          content={wbwDetail}
          placement="bottom"
          trigger="click"
          open={open}
          onOpenChange={handleClickChange}
        >
          {paliWord}
        </Popover>
        {videoIcon}
        {noteIcon}
        {bookMarkIcon}
      </div>
    );
  } else {
    //标点符号
    return (
      <div className="pali_shell" style={{ cursor: "unset" }}>
        {paliWord}
      </div>
    );
  }
};

export default Widget;

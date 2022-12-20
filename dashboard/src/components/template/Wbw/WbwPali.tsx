import { useState } from "react";
import { Popover, Typography, Button, Space } from "antd";
import {
  TagTwoTone,
  InfoCircleOutlined,
  CommentOutlined,
} from "@ant-design/icons";

import "./wbw.css";
import WbwDetail from "./WbwDetail";
import { IWbw } from "./WbwWord";
import { bookMarkColor } from "./WbwDetailBookMark";
import { PaliReal } from "../../../utils";
import WbwVideoButton from "./WbwVideoButton";
import CommentBox from "../../comment/CommentBox";

const { Paragraph } = Typography;
interface IWidget {
  data: IWbw;
  onSave?: Function;
}
const Widget = ({ data, onSave }: IWidget) => {
  const [click, setClicked] = useState(false);
  const [paliColor, setPaliColor] = useState("unset");
  const [isHover, setIsHover] = useState(false);
  const [hasComment, setHasComment] = useState(data.hasComment);

  const handleClickChange = (open: boolean) => {
    if (open) {
      setPaliColor("lightblue");
    } else {
      setPaliColor("unset");
    }
    setClicked(open);
  };

  const wbwDetail = (
    <WbwDetail
      data={data}
      onClose={() => {
        setPaliColor("unset");
        setClicked(false);
      }}
      onSave={(e: IWbw) => {
        if (typeof onSave !== "undefined") {
          onSave(e);
          setClicked(false);
          setPaliColor("unset");
        }
      }}
    />
  );

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
  const videoList = data.attachments?.filter((item) =>
    item.type?.includes("video")
  );
  const videoIcon = videoList ? (
    <WbwVideoButton
      video={videoList?.map((item) => {
        return {
          url: item.url ? item.url : "",
          type: item.type,
          title: item.name,
        };
      })}
    />
  ) : undefined;

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
  console.log(data);
  const discussionIcon =
    isHover || hasComment ? (
      <CommentBox
        resId={data.uid}
        resType="wbw"
        trigger={<CommentOutlined style={{ cursor: "pointer" }} />}
        onCommentCountChange={(count: number) => {
          if (count > 0) {
            setHasComment(true);
          } else {
            setHasComment(false);
          }
        }}
      />
    ) : (
      <></>
    );
  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    //非标点符号
    return (
      <div
        className="pali_shell"
        onMouseEnter={() => {
          setIsHover(true);
        }}
        onMouseLeave={() => {
          setIsHover(false);
        }}
      >
        <Popover
          content={wbwDetail}
          placement="bottom"
          trigger="click"
          open={click}
          onOpenChange={handleClickChange}
        >
          {paliWord}
        </Popover>
        {videoIcon}
        {noteIcon}
        {bookMarkIcon}
        {discussionIcon}
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

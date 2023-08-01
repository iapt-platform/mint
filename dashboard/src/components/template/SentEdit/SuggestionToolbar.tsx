import { Divider, Space, Tooltip, Typography } from "antd";
import { CommentOutlined, LikeOutlined } from "@ant-design/icons";
import { ISentence } from "../SentEdit";
import { useState } from "react";
import CommentBox from "../../discussion/DiscussionBox";
import SuggestionBox from "./SuggestionBox";
import PrAcceptButton from "./PrAcceptButton";
import { HandOutlinedIcon } from "../../../assets/icon";

const { Text, Paragraph } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  style?: React.CSSProperties;
  compact?: boolean;
  prOpen?: boolean;
  onPrClose?: Function;
  onAccept?: Function;
}
const SuggestionToolbarWidget = ({
  data,
  isPr = false,
  onAccept,
  style,
  prOpen = false,
  compact = false,
  onPrClose,
}: IWidget) => {
  const [CommentCount, setCommentCount] = useState<number | undefined>(
    data.suggestionCount?.discussion
  );
  const prButton = (
    <Space>
      <LikeOutlined />
      <Divider type="vertical" />
      <PrAcceptButton
        data={data}
        onAccept={(value: ISentence) => {
          if (typeof onAccept !== "undefined") {
            onAccept(value);
          }
        }}
      />
    </Space>
  );
  const normalButton = (
    <Space size={"small"}>
      <SuggestionBox
        open={prOpen}
        onClose={() => {
          if (typeof onPrClose !== "undefined") {
            onPrClose();
          }
        }}
        data={data}
        trigger={
          <Tooltip title="修改建议">
            <HandOutlinedIcon style={{ cursor: "pointer" }} />
          </Tooltip>
        }
      />
      {compact ? undefined : <Divider type="vertical" />}
      <CommentBox
        resId={data.id}
        resType="sentence"
        trigger={
          <Tooltip title="讨论">
            <CommentOutlined style={{ cursor: "pointer" }} />
          </Tooltip>
        }
        onCommentCountChange={(count: number) => {
          setCommentCount(count);
        }}
      />
      {CommentCount}
      {compact ? undefined : <Divider type="vertical" />}
      {compact ? undefined : <Text copyable={{ text: data.content }}></Text>}
    </Space>
  );
  return (
    <Paragraph type="secondary" style={style}>
      {isPr ? prButton : normalButton}
    </Paragraph>
  );
};

export default SuggestionToolbarWidget;

import { Divider, Space, Tooltip, Typography } from "antd";
import { CommentOutlined, LikeOutlined } from "@ant-design/icons";
import { ISentence } from "../SentEdit";
import { useState } from "react";
import CommentBox from "../../discussion/CommentBox";
import SuggestionBox from "./SuggestionBox";
import PrAcceptButton from "./PrAcceptButton";
import { HandOutlinedIcon } from "../../../assets/icon";

const { Text } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  onAccept?: Function;
}
const SuggestionToolbarWidget = ({ data, isPr = false, onAccept }: IWidget) => {
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
    <Space>
      <SuggestionBox
        data={data}
        trigger={
          <Tooltip title="修改建议">
            <HandOutlinedIcon style={{ cursor: "pointer" }} />
          </Tooltip>
        }
      />
      <Divider type="vertical" />
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
      <Divider type="vertical" />
      <Text copyable={{ text: data.content }}></Text>
    </Space>
  );
  return <Text type="secondary">{isPr ? prButton : normalButton}</Text>;
};

export default SuggestionToolbarWidget;

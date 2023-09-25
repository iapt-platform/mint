import { Divider, Space, Tooltip, Typography } from "antd";
import { LikeOutlined } from "@ant-design/icons";
import { ISentence } from "../SentEdit";
import { useEffect, useState } from "react";
import CommentBox from "../../discussion/DiscussionDrawer";
import SuggestionBox from "./SuggestionBox";
import PrAcceptButton from "./PrAcceptButton";
import { CommentOutlinedIcon, HandOutlinedIcon } from "../../../assets/icon";
import store from "../../../store";
import { count, show } from "../../../reducers/discussion";
import { useAppSelector } from "../../../hooks";
import { openPanel } from "../../../reducers/right-panel";

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
  const discussionCount = useAppSelector(count);

  useEffect(() => {
    if (
      discussionCount?.resType === "sentence" &&
      discussionCount.resId === data.id
    ) {
      setCommentCount(discussionCount.count);
    }
  }, [data.id, discussionCount]);
  const prNumber = data.suggestionCount?.suggestion;
  return (
    <Paragraph type="secondary" style={style}>
      {isPr ? (
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
      ) : (
        <Space size={"small"}>
          <Space
            style={{
              cursor: "pointer",
              color: prNumber && prNumber > 0 ? "#1890ff" : "unset",
            }}
            onClick={(event) => {
              store.dispatch(
                show({
                  type: "pr",
                  sent: data,
                })
              );
              store.dispatch(openPanel("suggestion"));
            }}
          >
            <Tooltip title="修改建议">
              <HandOutlinedIcon />
            </Tooltip>
            {prNumber}
          </Space>
          {compact ? undefined : <Divider type="vertical" />}
          <Tooltip title="讨论">
            <Space
              size={"small"}
              style={{
                cursor: "pointer",
                color: CommentCount && CommentCount > 0 ? "#1890ff" : "unset",
              }}
              onClick={(event) => {
                store.dispatch(
                  show({
                    type: "discussion",
                    resId: data.id,
                    resType: "sentence",
                  })
                );
                store.dispatch(openPanel("discussion"));
              }}
            >
              <CommentOutlinedIcon />
              {CommentCount}
            </Space>
          </Tooltip>
          <CommentBox
            resId={data.id}
            resType="sentence"
            trigger={<></>}
            onCommentCountChange={(count: number) => {
              setCommentCount(count);
            }}
          />

          {compact ? undefined : <Divider type="vertical" />}
          {compact ? undefined : (
            <Text copyable={{ text: data.content ? data.content : "" }}></Text>
          )}
        </Space>
      )}
    </Paragraph>
  );
};

export default SuggestionToolbarWidget;

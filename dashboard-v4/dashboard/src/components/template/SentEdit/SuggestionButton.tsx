import { Space, Tooltip } from "antd";
import { LikeOutlined, DeleteOutlined } from "@ant-design/icons";

import { ISentence } from "../SentEdit";
import { HandOutlinedIcon } from "../../../assets/icon";
import SuggestionPopover from "./SuggestionPopover";
import store from "../../../store";
import { openPanel } from "../../../reducers/right-panel";
import { show } from "../../../reducers/discussion";

export const prOpen = (data: ISentence) => {
  store.dispatch(
    show({
      type: "pr",
      sent: data,
    })
  );
  store.dispatch(openPanel("suggestion"));
};

interface IWidget {
  data: ISentence;
  hideCount?: boolean;
  hideInZero?: boolean;
}

const SuggestionButton = ({
  data,
  hideCount = false,
  hideInZero = false,
}: IWidget) => {
  const prNumber = data.suggestionCount?.suggestion;

  return hideInZero && prNumber === 0 ? (
    <></>
  ) : (
    <Space
      style={{
        cursor: "pointer",
        color: prNumber && prNumber > 0 ? "#1890ff" : "unset",
      }}
      onClick={(event) => {
        prOpen(data);
      }}
    >
      <Tooltip title="修改建议">
        <HandOutlinedIcon />
      </Tooltip>
      <SuggestionPopover
        book={data.book}
        para={data.para}
        start={data.wordStart}
        end={data.wordEnd}
        channelId={data.channel.id}
      />
      {hideCount ? <></> : prNumber}
    </Space>
  );
};

export default SuggestionButton;

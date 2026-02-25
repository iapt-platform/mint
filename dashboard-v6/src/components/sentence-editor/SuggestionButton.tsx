import { Space, Tooltip } from "antd";

import type { ISentence } from "../../api/Corpus";
import { HandOutlinedIcon } from "../../assets/icon";
import SuggestionPopover from "./SuggestionPopover";
import { prOpen } from "./utils";

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
      onClick={() => {
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

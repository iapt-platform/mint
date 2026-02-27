import { Popover } from "antd";
import { useMemo, useState } from "react";
import SentCell from "./SentCell";
import type { ISentence } from "../../api/sentence";
import { useAppSelector } from "../../hooks";
import { prInfo, refresh } from "../../reducers/pr-load";
import store from "../../store";

interface IWidget {
  book: number;
  para: number;
  start: number;
  end: number;
  channelId: string;
}

const SuggestionPopoverWidget = ({
  book,
  para,
  start,
  end,
  channelId,
}: IWidget) => {
  const [open, setOpen] = useState(false);
  const pr = useAppSelector(prInfo);

  const sentData = useMemo<ISentence | undefined>(() => {
    if (
      pr &&
      book === pr.book &&
      para === pr.paragraph &&
      start === pr.word_start &&
      end === pr.word_end &&
      channelId === pr.channel.id
    ) {
      return {
        id: pr.id,
        content: pr.content,
        html: pr.html,
        book: pr.book,
        para: pr.paragraph,
        wordStart: pr.word_start,
        wordEnd: pr.word_end,
        editor: pr.editor,
        channel: { name: pr.channel.name, id: pr.channel.id },
        updateAt: pr.updated_at,
      };
    }
    return undefined;
  }, [book, channelId, end, para, pr, start]);

  // Derive open state from sentData so no effect is needed
  const isOpen = open && !!sentData;

  const handleOpenChange = (newOpen: boolean) => {
    setOpen(newOpen);
    if (!newOpen) {
      store.dispatch(refresh(null));
    }
  };

  return (
    <Popover
      placement="bottomRight"
      arrow={{ pointAtCenter: true }}
      content={
        <div>
          <SentCell value={sentData} key={1} isPr={true} showDiff={false} />
        </div>
      }
      title={`${sentData?.editor.nickName}提交的修改建议`}
      trigger="click"
      open={isOpen}
      onOpenChange={handleOpenChange}
    >
      <span></span>
    </Popover>
  );
};

export default SuggestionPopoverWidget;

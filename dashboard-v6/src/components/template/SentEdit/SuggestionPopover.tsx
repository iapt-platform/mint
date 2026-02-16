import { Popover } from "antd";
import { useEffect, useState } from "react";
import SentCell from "./SentCell";
import { ISentence } from "../SentEdit";
import { useAppSelector } from "../../../hooks";
import { prInfo, refresh } from "../../../reducers/pr-load";
import store from "../../../store";

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
  const [sentData, setSentData] = useState<ISentence>();
  const pr = useAppSelector(prInfo);

  useEffect(() => {
    if (pr) {
      if (
        book === pr.book &&
        para === pr.paragraph &&
        start === pr.word_start &&
        end === pr.word_end &&
        channelId === pr.channel.id
      ) {
        setSentData({
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
        });
        setOpen(true);
      }
    }
  }, [book, channelId, end, para, pr, start]);

  const handleOpenChange = (newOpen: boolean) => {
    setOpen(newOpen);
    if (newOpen === false) {
      store.dispatch(refresh(null));
    }
  };
  return (
    <Popover
      placement="bottomRight"
      arrowPointAtCenter
      content={
        <div>
          <SentCell value={sentData} key={1} isPr={true} showDiff={false} />
        </div>
      }
      title={`${sentData?.editor.nickName}提交的修改建议`}
      trigger="click"
      open={open}
      onOpenChange={handleOpenChange}
    >
      <span></span>
    </Popover>
  );
};

export default SuggestionPopoverWidget;

import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { prInfo } from "../../../reducers/pr-load";

interface IWidget {
  book: number;
  para: number;
  start: number;
  end: number;
  channelId: string;
  children?: React.ReactNode;
}
const SuggestionFocusWidget = ({
  book,
  para,
  start,
  end,
  channelId,
  children,
}: IWidget) => {
  const pr = useAppSelector(prInfo);
  const [highlight, setHighlight] = useState(false);
  useEffect(() => {
    if (pr) {
      if (
        book === pr.book &&
        para === pr.paragraph &&
        start === pr.word_start &&
        end === pr.word_end &&
        channelId === pr.channel.id
      ) {
        setHighlight(true);
      } else {
        setHighlight(false);
      }
    } else {
      setHighlight(false);
    }
  }, [book, channelId, end, para, pr, start]);
  return (
    <span
      style={{
        backgroundColor: highlight ? "rgb(255 255 0 / 20%)" : undefined,
        width: "100%",
      }}
    >
      {children}
    </span>
  );
};

export default SuggestionFocusWidget;

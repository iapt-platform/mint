import { useEffect, useRef, useState } from "react";
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
  const divRef = useRef<HTMLDivElement>(null);

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
        divRef.current?.scrollIntoView({
          behavior: "smooth",
          block: "center",
          inline: "nearest",
        });
      } else {
        setHighlight(false);
      }
    } else {
      setHighlight(false);
    }
  }, [book, channelId, end, para, pr, start]);
  return (
    <div
      ref={divRef}
      style={{
        backgroundColor: highlight ? "rgb(255 255 0 / 20%)" : undefined,
        width: "100%",
      }}
    >
      {children}
    </div>
  );
};

export default SuggestionFocusWidget;

import { useEffect, useRef } from "react";
import { useAppSelector } from "../../hooks";
import { prInfo } from "../../reducers/pr-load";

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
  const divRef = useRef<HTMLDivElement>(null);

  // 直接派生，无需 useState + useEffect
  const highlight =
    !!pr &&
    book === pr.book &&
    para === pr.paragraph &&
    start === pr.word_start &&
    end === pr.word_end &&
    channelId === pr.channel.id;

  // 仅负责滚动这一"外部副作用"，不再 setState
  useEffect(() => {
    if (highlight) {
      divRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "center",
        inline: "nearest",
      });
    }
  }, [highlight]);

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

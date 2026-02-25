import { Divider, Space } from "antd";
import SuggestionButton from "./SuggestionButton";
import DiscussionButton from "../discussion/DiscussionButton";
import type { ISentence } from "../../api/Corpus";
import {
  type MouseEventHandler,
  useCallback,
  useLayoutEffect,
  useRef,
  useState,
} from "react";

interface IWidget {
  data: ISentence;
  compact?: boolean;
  float?: boolean;
  hideCount?: boolean;
  hideInZero?: boolean;
  onMouseEnter?: MouseEventHandler | undefined;
  onMouseLeave?: MouseEventHandler | undefined;
}

interface IFloatPosition {
  left: number;
  width: number;
}

const InteractiveButton = ({
  data,
  compact = false,
  float = false,
  hideCount = false,
  hideInZero = false,
  onMouseEnter,
  onMouseLeave,
}: IWidget) => {
  const [position, setPosition] = useState<IFloatPosition>({
    left: 0,
    width: 0,
  });
  const observerRef = useRef<ResizeObserver | null>(null);

  const updatePosition = useCallback((el: Element) => {
    const rect = el.getBoundingClientRect();
    setPosition({ left: rect.left, width: rect.width });
  }, []);

  useLayoutEffect(() => {
    if (!float) return;

    const targetNode = document.getElementsByClassName("article_shell")[0];
    if (!targetNode) return;

    // 初始化位置（在 layout effect 中读取 DOM 是合理的，
    // 但 setState 要放在 callback/microtask 中避免 lint 警告）
    const raf = requestAnimationFrame(() => {
      updatePosition(targetNode);
    });

    observerRef.current = new ResizeObserver((entries) => {
      for (const entry of entries) {
        const rect = entry.target.getBoundingClientRect();
        setPosition((prev) => {
          const newWidth = entry.contentRect.width;
          return prev.width === newWidth && prev.left === rect.left
            ? prev
            : { left: rect.left, width: newWidth };
        });
      }
    });

    observerRef.current.observe(targetNode);

    const handleResize = () => updatePosition(targetNode);
    window.addEventListener("resize", handleResize);

    return () => {
      cancelAnimationFrame(raf);
      observerRef.current?.disconnect();
      observerRef.current = null;
      window.removeEventListener("resize", handleResize);
    };
  }, [float, updatePosition]);

  const ButtonInner = (
    <Space size="small" onMouseEnter={onMouseEnter} onMouseLeave={onMouseLeave}>
      <SuggestionButton
        data={data}
        hideCount={hideCount}
        hideInZero={hideInZero}
      />
      {!compact && <Divider type="vertical" />}
      <DiscussionButton
        hideCount={hideCount}
        hideInZero={hideInZero}
        initCount={data.suggestionCount?.discussion}
        resId={data.id}
      />
    </Space>
  );

  if (!float) return ButtonInner;

  return (
    <span
      className="sent_read_interactive_button"
      style={{ position: "absolute", left: position.left + position.width }}
    >
      {ButtonInner}
    </span>
  );
};

export default InteractiveButton;

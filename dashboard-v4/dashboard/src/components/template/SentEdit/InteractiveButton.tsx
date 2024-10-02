import { Divider, Space } from "antd";
import SuggestionButton from "./SuggestionButton";
import DiscussionButton from "../../discussion/DiscussionButton";
import { ISentence } from "../SentEdit";
import { MouseEventHandler, useEffect, useState } from "react";

interface IWidget {
  data: ISentence;
  compact?: boolean;
  float?: boolean;
  hideCount?: boolean;
  hideInZero?: boolean;
  onMouseEnter?: MouseEventHandler | undefined;
  onMouseLeave?: MouseEventHandler | undefined;
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
  const [left, setLeft] = useState(0);
  const [width, setWidth] = useState(0);

  useEffect(() => {
    // 获取目标元素
    const targetNode = document.getElementsByClassName("article_shell")[0];
    if (!targetNode) {
      return;
    }
    const rect = targetNode.getBoundingClientRect();
    setLeft(rect.left);
    setWidth(rect.width);
    // 创建ResizeObserver实例并传入回调函数
    const resizeObserver = new ResizeObserver((entries) => {
      for (let entry of entries) {
        const { width, height } = entry.contentRect;
        //console.log(`Element size: ${width}px x ${height}px`);
        setWidth((origin) => width);
      }
    });

    // 观察目标节点
    resizeObserver.observe(targetNode);

    // 定义一个函数来处理窗口大小变化
    function handleResize() {
      // 获取窗口宽度
      const windowWidth = window.innerWidth;
      // 在控制台中输出新的窗口宽度
      //console.log(`新的窗口宽度是：${windowWidth}px`);

      // 假设你有一个div元素，它的id是"myDiv"
      const myDiv = document.getElementsByClassName("article_shell")[0];
      if (!myDiv) {
        return;
      }
      // 使用getBoundingClientRect()方法获取元素的位置和大小
      const rect = myDiv.getBoundingClientRect();

      // 从返回的对象中获取left属性
      const leftPosition = rect.left;

      // 输出left位置
      //console.log(`div的left位置是：${leftPosition}px`);
      setLeft(leftPosition);

      // 在这里，你可以根据窗口宽度来调整页面布局或样式
    }

    // 为resize事件添加监听器
    window.addEventListener("resize", handleResize);
  }, []);

  const ButtonInner = (
    <Space
      size={"small"}
      onMouseEnter={onMouseEnter}
      onMouseLeave={onMouseLeave}
    >
      <SuggestionButton
        data={data}
        hideCount={hideCount}
        hideInZero={hideInZero}
      />
      {compact ? undefined : <Divider type="vertical" />}
      <DiscussionButton
        hideCount={hideCount}
        hideInZero={hideInZero}
        initCount={data.suggestionCount?.discussion}
        resId={data.id}
      />
    </Space>
  );

  return float ? (
    <span
      className="sent_read_interactive_button"
      style={{ position: "absolute", left: left + width }}
    >
      {ButtonInner}
    </span>
  ) : (
    ButtonInner
  );
};

export default InteractiveButton;

import { CloseOutlined } from "@ant-design/icons";
import { Button, Tabs } from "antd";
import { useCallback, useState } from "react";
import { useIntl } from "react-intl";
import DictComponent from "../../dict/DictComponent";
import { useSplitLayout } from "../../general/SplitLayout";
import styles from "./DictPanel.module.css";

/**
 * 字典面板
 * 封装成独立组件，让 React 管理生命周期，
 * 避免内联 JSX 在 rightTabs 重建时丢失内部状态。
 *
 * 顶栏是一行 tabs + 最右侧关闭按钮（替代右边栏通用的「字典」标题栏）。
 * 点 + 新开一个空白查词 tab；全局查词（点经文里的词）只送到当前 tab，
 * 其余 tab 保持各自的词，方便对照。tab 标题显示该 tab 查的词，没查过显示「字典」。
 */

/**
 * 从查词串取 tab 标题。普通词原样返回；高级格式（`type:term id:xxx`）
 * 只认 `word:`，取不到返回 undefined，由调用方保留原标题。
 */
function labelOf(value?: string): string | undefined {
  if (!value) return undefined;
  if (!value.includes(":")) return value;
  return value.match(/(?:^|\s)word:(\S+)/)?.[1];
}

export default function DictPanel() {
  const intl = useIntl();
  const { closeRightPanel } = useSplitLayout();
  const [tabs, setTabs] = useState<string[]>(["1"]);
  const [activeKey, setActiveKey] = useState("1");
  const [nextKey, setNextKey] = useState(2);
  /** tab key → 该 tab 查的词 */
  const [labels, setLabels] = useState<Record<string, string>>({});

  // 值没变时返回原对象，React 跳过重渲染，子组件每次渲染回调也不会循环
  const setWord = useCallback((key: string, word?: string) => {
    const label = labelOf(word);
    if (label === undefined) return;
    setLabels((prev) =>
      prev[key] === label ? prev : { ...prev, [key]: label },
    );
  }, []);

  const title = intl.formatMessage({ id: "columns.library.dict.title" });

  const add = () => {
    const key = String(nextKey);
    setNextKey(nextKey + 1);
    setTabs([...tabs, key]);
    setActiveKey(key);
  };

  const remove = (key: string) => {
    const index = tabs.indexOf(key);
    const rest = tabs.filter((t) => t !== key);
    setTabs(rest);
    if (key === activeKey) {
      setActiveKey(rest[Math.max(0, index - 1)]);
    }
  };

  return (
    <div className="dict_component">
      <Tabs
        className={styles.tabs}
        type="editable-card"
        size="small"
        activeKey={activeKey}
        onChange={setActiveKey}
        onEdit={(key, action) => {
          if (action === "add") add();
          else if (typeof key === "string") remove(key);
        }}
        tabBarExtraContent={{
          right: (
            <Button
              type="text"
              size="small"
              icon={<CloseOutlined />}
              onClick={closeRightPanel}
              title="关闭面板"
            />
          ),
        }}
        items={tabs.map((key) => ({
          key,
          label: (
            <span className={styles.label} title={labels[key]}>
              {labels[key] ?? title}
            </span>
          ),
          closable: tabs.length > 1,
          children: (
            <DictComponent
              follow={key === activeKey}
              blank={key !== "1"}
              onWordChange={(word) => setWord(key, word)}
            />
          ),
        }))}
      />
    </div>
  );
}

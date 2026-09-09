import { Space, Typography } from "antd";
import { useMemo, useEffect } from "react";
import { RobotOutlined } from "@ant-design/icons";

import { TermIcon } from "../../assets/icon";
import { useAppSelector } from "../../hooks";
import { getTerm } from "../../reducers/term-vocabulary";
import { PaliToEn } from "../../utils";
import { getPaliBase } from "./PaliEnding";

const { Text } = Typography;

interface IWordWithEn {
  word: string;
  en: string;
  isBase?: boolean;
  isTerm?: boolean;
}

interface IWidget {
  items?: string[];
  searchKey?: string;
  maxItem?: number;
  visible?: boolean;
  currIndex?: number;
  onChange?: (word: string) => void;
  onSelect?: (word: string) => void;
  /** 当前可见候选项数量，供父组件限制上下键范围 */
  onCount?: (count: number) => void;
}

const TermTextAreaMenuWidget = ({
  items,
  searchKey = "",
  maxItem = 10,
  visible = false,
  currIndex = 0,
  onChange,
  onSelect,
  onCount,
}: IWidget) => {
  const sysTerms = useAppSelector(getTerm);

  /**
   * ✅ wordList 改为 useMemo
   */
  const wordList: IWordWithEn[] = useMemo(() => {
    const parents: string[] = [];
    let mWords: IWordWithEn[] = [];

    if (items) {
      mWords = items.map((item) => ({
        word: item,
        en: PaliToEn(item),
      }));

      items.forEach((value) => {
        getPaliBase(value).forEach((base) => {
          if (!parents.includes(base) && !items.includes(base)) {
            parents.push(base);
          }
        });
      });
    }

    const term = sysTerms ? sysTerms.map((item) => item.word) : [];

    const parentTerm = parents.map((item) => {
      const inSystem = term.includes(item);
      return {
        word: item,
        en: PaliToEn(item),
        isBase: !inSystem,
        isTerm: inSystem,
      };
    });

    const sysTerm = term
      .filter((value) => !parents.includes(value))
      .sort((a, b) => a.length - b.length)
      .map((item) => ({
        word: item,
        en: PaliToEn(item),
        isTerm: true,
      }));

    // 术语表可能有同一个 word 的多条记录（不同 tag/meaning），
    // 句子单词也可能同时出现在术语表里，这里按 word 统一去重，
    // 保留优先级 parentTerm > mWords > sysTerm。
    const unique = new Map<string, IWordWithEn>();
    [...parentTerm, ...mWords, ...sysTerm].forEach((item) => {
      if (!unique.has(item.word)) {
        unique.set(item.word, item);
      }
    });

    return Array.from(unique.values());
  }, [items, sysTerms]);

  /**
   * ✅ filtered 改为 useMemo
   */
  const filtered = useMemo(() => {
    if (!searchKey) return wordList;

    return wordList.filter(
      (value) => value.en.slice(0, searchKey.length) === searchKey
    );
  }, [wordList, searchKey]);

  /**
   * ✅ 只有真正副作用才用 useEffect
   */
  const visibleCount = Math.min(filtered.length, maxItem);

  useEffect(() => {
    onCount?.(visibleCount);
  }, [visibleCount, onCount]);

  useEffect(() => {
    if (!visibleCount || !onChange) return;

    const index = currIndex < visibleCount ? currIndex : visibleCount - 1;

    onChange(filtered[index].word);
  }, [currIndex, filtered, visibleCount, onChange]);

  if (!visible) return null;

  return (
    <>
      <div className="term_at_menu_input">{`${searchKey}|`}</div>

      <ul className="term_at_menu_ul">
        {filtered.slice(0, maxItem).map((item, index) => (
          <li
            key={item.word}
            className={index === currIndex ? "term_focus" : undefined}
            onClick={() => onSelect?.(item.word)}
          >
            <Space style={{ width: "100%", justifyContent: "space-between" }}>
              <Text strong={item.isBase || item.isTerm}>{item.word}</Text>

              {item.isTerm ? (
                <TermIcon />
              ) : item.isBase ? (
                <RobotOutlined />
              ) : null}
            </Space>
          </li>
        ))}
      </ul>
    </>
  );
};

export default TermTextAreaMenuWidget;

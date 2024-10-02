import { Space, Typography } from "antd";
import { useEffect, useState } from "react";
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
  onChange?: Function;
  onSelect?: Function;
}
const TermTextAreaMenuWidget = ({
  items,
  searchKey = "",
  maxItem = 10,
  visible = false,
  currIndex = 0,
  onChange,
  onSelect,
}: IWidget) => {
  const [filtered, setFiltered] = useState<IWordWithEn[]>();
  const [wordList, setWordList] = useState<IWordWithEn[]>();
  const sysTerms = useAppSelector(getTerm);
  console.log("items", items);

  useEffect(() => {
    let parents: string[] = [];
    let mWords: IWordWithEn[] = [];
    //本句单词
    if (items) {
      mWords = items?.map((item) => {
        return {
          word: item,
          en: PaliToEn(item),
        };
      });

      //计算这些单词的base

      items?.forEach((value) => {
        getPaliBase(value).forEach((base) => {
          if (!parents.includes(base) && !items.includes(base)) {
            parents.push(base);
          }
        });
      });
    }

    const term = sysTerms ? sysTerms?.map((item) => item.word) : [];
    //本句单词parent
    const parentTerm = parents?.map((item) => {
      const inSystem = term.includes(item);
      return {
        word: item,
        en: PaliToEn(item),
        isBase: !inSystem,
        isTerm: inSystem,
      };
    });

    //社区术语
    const sysTerm = term
      .filter((value) => !parents.includes(value))
      .sort((a, b) => a.length - b.length)
      .map((item) => {
        return {
          word: item,
          en: PaliToEn(item),
          isTerm: true,
        };
      });

    setWordList([...parentTerm, ...mWords, ...sysTerm]);

    //此处千万不能加其他dependency 否则会引起无限循环
  }, [items]);

  useEffect(() => {
    const filteredItems =
      searchKey !== ""
        ? wordList?.filter(
            (value) => value.en.slice(0, searchKey.length) === searchKey
          )
        : wordList;
    setFiltered(filteredItems);
  }, [wordList, searchKey]);

  useEffect(() => {
    if (filtered && filtered.length > 0 && typeof onChange !== "undefined") {
      if (currIndex < filtered.length) {
        onChange(filtered[currIndex].word);
      } else {
        onChange(filtered[filtered.length - 1].word);
      }
    }
  }, [currIndex, filtered]);

  if (visible) {
    return (
      <>
        <div className="term_at_menu_input" key="head">
          {`${searchKey}|`}
        </div>
        <ul className="term_at_menu_ul">
          {filtered?.slice(0, maxItem).map((item, index) => {
            return (
              <li
                key={index}
                className={index === currIndex ? "term_focus" : undefined}
                onClick={() => {
                  if (typeof onSelect !== "undefined") {
                    onSelect(item.word);
                  }
                }}
              >
                <Space
                  style={{ width: "100%", justifyContent: "space-between" }}
                >
                  <Text strong={item.isBase || item.isTerm}>{item.word}</Text>
                  {item.isTerm ? (
                    <TermIcon />
                  ) : item.isBase ? (
                    <RobotOutlined />
                  ) : undefined}
                </Space>
              </li>
            );
          })}
        </ul>
      </>
    );
  } else {
    return <></>;
  }
};

export default TermTextAreaMenuWidget;

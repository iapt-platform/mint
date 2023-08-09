import { useEffect, useState } from "react";
import { PaliToEn } from "../../utils";
import { getPaliBase } from "./PaliEnding";

interface IWordWithEn {
  word: string;
  en: string;
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
  items = [],
  searchKey,
  maxItem = 10,
  visible = false,
  currIndex = 0,
  onChange,
  onSelect,
}: IWidget) => {
  const [filtered, setFiltered] = useState<IWordWithEn[]>();
  const [wordList, setWordList] = useState<IWordWithEn[]>();

  useEffect(() => {
    //计算这些单词的base
    let parents: string[] = [];
    items?.forEach((value) => {
      getPaliBase(value).forEach((base) => {
        if (!parents.includes(base) && !items.includes(base)) {
          parents.push(base);
        }
      });
    });
    const wordList = [...parents, ...items];
    const mWords = wordList?.map((item) => {
      return {
        word: item,
        en: PaliToEn(item),
      };
    });
    console.log("words", mWords);
    setWordList(mWords);
  }, [items]);

  useEffect(() => {
    const filteredItems = searchKey
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
          {filtered?.map((item, index) => {
            if (index < maxItem) {
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
                  {item.word}
                </li>
              );
            } else {
              return undefined;
            }
          })}
        </ul>
      </>
    );
  } else {
    return <></>;
  }
};

export default TermTextAreaMenuWidget;

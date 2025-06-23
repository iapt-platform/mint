import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import Lookup from "../../dict/Lookup";
import { NissayaCardPop } from "../../general/NissayaCard";
import {
  convertMyanmarPaliToRoman,
  convertMyanmarPaliArrayToRoman,
} from "../../../myanmar-pali-converter";
import { Tooltip } from "antd";

export interface IMeaning {
  base: string;
  ending?: string[];
}

export const nissayaBase = (item: string, endings: string[]): IMeaning => {
  let word = item
    .trim()
    .replaceAll("။", "")
    .replaceAll("[}", "")
    .replaceAll("]", "")
    .replaceAll("(", "")
    .replaceAll(")", "")
    .replaceAll("၊", "")
    .replaceAll(",", "")
    .replaceAll(".", "");

  let end: string[] = [];
  for (let loop = 0; loop < 3; loop++) {
    for (let i = 0; i < word.length; i++) {
      const ending = word.slice(i);
      if (endings?.includes(ending)) {
        end.unshift(word.slice(i));
        word = word.slice(0, i);
      }
    }
  }
  return {
    base: word,
    ending: end,
  };
};

interface IWidget {
  text?: string;
  code?: string;
}

const NissayaMeaningWidget = ({ text, code = "my" }: IWidget) => {
  const [words, setWords] = useState<IMeaning[]>();
  const endings = useAppSelector(getEnding);

  useEffect(() => {
    if (typeof text === "undefined" || typeof endings === "undefined") {
      return;
    }

    const mWords: IMeaning[] = text.split(" ").map((item) => {
      return nissayaBase(item, endings);
    });
    setWords(mWords);
  }, [endings, text]);

  if (typeof text === "undefined") {
    return <></>;
  }

  return (
    <span>
      {words?.map((item, id) => {
        const result = convertMyanmarPaliToRoman(item.base);
        return (
          <span key={id}>
            <Lookup search={item.base}>
              <Tooltip title={result} mouseEnterDelay={2}>
                {item.base}
              </Tooltip>
            </Lookup>
            {item.ending?.map((item, id) => {
              return <NissayaCardPop text={item} key={id} trigger={item} />;
            })}{" "}
          </span>
        );
      })}
    </span>
  );
};

export default NissayaMeaningWidget;

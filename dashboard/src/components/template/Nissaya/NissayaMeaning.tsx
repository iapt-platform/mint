import { Space } from "antd";
import React, { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import { NissayaCardPop } from "../../general/NissayaCard";

interface IMeaning {
  base: string;
  ending?: string[];
}

interface IWidget {
  text?: string;
  code?: string;
}

const NissayaMeaningWidget = ({ text, code = "my" }: IWidget) => {
  const [words, setWords] = useState<IMeaning[]>();
  const endings = useAppSelector(getEnding);

  useEffect(() => {
    if (typeof text === "undefined") {
      return;
    }
    console.log("ending", endings);

    const mWords: IMeaning[] = text.split(" ").map((item) => {
      let word = item.replaceAll("။", "");
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
    });
    setWords(mWords);
  }, [endings, text]);

  if (typeof text === "undefined") {
    return <></>;
  }
  return (
    <Space>
      {words?.map((item, id) => {
        return (
          <span key={id}>
            {item.base}
            {item.ending?.map((item, id) => {
              return <NissayaCardPop text={item} key={id} trigger={item} />;
            })}
          </span>
        );
      })}
    </Space>
  );
};

export default NissayaMeaningWidget;

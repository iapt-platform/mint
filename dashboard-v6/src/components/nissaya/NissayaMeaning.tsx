import { useEffect, useState } from "react";
import { Tag, Tooltip, Typography } from "antd";
import { useAppSelector } from "../../hooks";

import { getEnding } from "../../reducers/nissaya-ending-vocabulary";
import Lookup from "../dict/Lookup";
import { NissayaCardPop } from "./NissayaCard";
import { my_to_roman } from "../../utils/code/my";
import { nissayaBase } from "./utils";

const { Text } = Typography;

export interface IMeaning {
  base: string;
  ending?: string[];
}

interface IWidget {
  text?: string;
  code?: string;
}

const NissayaMeaningWidget = ({ text }: IWidget) => {
  const [words, setWords] = useState<IMeaning[]>();
  const endings = useAppSelector(getEnding);

  const match = text?.match(/#(\d+)%/);
  const cf = match ? parseInt(match[1]) : null;

  useEffect(() => {
    if (typeof text === "undefined" || typeof endings === "undefined") {
      return;
    }

    const _text = text.replace(/#\d+%/, "");
    const mWords: IMeaning[] = _text.split(" ").map((item) => {
      return nissayaBase(item, endings);
    });
    setWords(mWords);
  }, [endings, text]);

  if (typeof text === "undefined") {
    return <></>;
  }

  return (
    <Text>
      <>
        {words?.map((item, id) => {
          const result = my_to_roman(item.base);
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
      </>
      <>{cf !== null && cf < 90 ? <Tag color="red">{cf}</Tag> : undefined}</>
    </Text>
  );
};

export default NissayaMeaningWidget;

import { message } from "antd";
import { useEffect, useState } from "react";

import { useAppSelector } from "../../hooks";
import { mode } from "../../reducers/article-mode";
import { post } from "../../request";
import { ArticleMode } from "../article/Article";
import WbwWord, { IWbw, IWbwFields, WbwElement } from "./Wbw/WbwWord";

interface IMagicDictRequest {
  book: number;
  para: number;
  word_start: number;
  word_end: number;
  data: IWbw[];
  channel_id: string;
}
interface IMagicDictResponse {
  ok: boolean;
  message: string;
  data: IWbw[];
}

interface IWbwXml {
  id: string;
  pali: WbwElement<string>;
  real?: WbwElement<string>;
  type?: WbwElement<string>;
  gramma?: WbwElement<string>;
  mean?: WbwElement<string>;
  org?: WbwElement<string>;
  om?: WbwElement<string>;
  case?: WbwElement<string>;
  parent?: WbwElement<string>;
  pg?: WbwElement<string>;
  parent2?: WbwElement<string>;
  rela?: WbwElement<string>;
  lock?: boolean;
  bmt?: WbwElement<string>;
  bmc?: WbwElement<number>;
  cf: number;
}
interface IWbwUpdateResponse {
  ok: boolean;
  message: string;
  data: { rows?: IWbwXml[]; count: number };
}
interface IWbwRequest {
  book: number;
  para: number;
  sn: number;
  channel_id: string;
  data: IWbwXml[];
}
interface IWidget {
  data: IWbw[];
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelId: string;
  display?: "block" | "inline";
  fields?: IWbwFields;
  magicDict?: string;

  onChange?: Function;
}
export const WbwSentCtl = ({
  data,
  channelId,
  book,
  para,
  wordStart,
  wordEnd,
  display = "inline",
  fields,
  magicDict,
  onChange,
}: IWidget) => {
  const [wordData, setWordData] = useState<IWbw[]>(data);
  const [wbwMode, setWbwMode] = useState(display);
  const [fieldDisplay, setFieldDisplay] = useState(fields);
  const [displayMode, setDisplayMode] = useState<ArticleMode>();
  const newMode = useAppSelector(mode);
  //console.log("wbw sent ", data);
  useEffect(() => {
    setDisplayMode(newMode);
    switch (newMode) {
      case "edit":
        setWbwMode("block");

        setFieldDisplay({
          meaning: true,
          factors: false,
          factorMeaning: false,
          case: false,
        });
        break;
      case "wbw":
        setWbwMode("block");
        setFieldDisplay({
          meaning: true,
          factors: true,
          factorMeaning: true,
          case: true,
        });
        break;
    }
  }, [newMode]);

  useEffect(() => {
    if (typeof magicDict === "undefined") {
      return;
    }
    const url = `/v2/wbwlookup`;
    console.log("magic dict url", url);
    post<IMagicDictRequest, IMagicDictResponse>(url, {
      book: book,
      para: para,
      word_start: wordStart,
      word_end: wordEnd,
      data: wordData,
      channel_id: channelId,
    }).then((json) => {
      if (json.ok) {
        console.log("magic dict result", json.data);
        setWordData(json.data);
      } else {
        console.error(json.message);
      }
    });
  }, [magicDict]);
  return (
    <div style={{ display: "flex", flexWrap: "wrap" }}>
      {wordData.map((item, id) => {
        return (
          <WbwWord
            data={item}
            key={id}
            mode={displayMode}
            display={wbwMode}
            fields={fieldDisplay}
            onChange={(e: IWbw) => {
              console.log("word changed", e);
              let newData = [...wordData];
              newData.forEach((value, index, array) => {
                if (value.sn.join() === e.sn.join()) {
                  console.log("found", e.sn);
                  array[index] = e;
                }
              });
              console.log("new data", newData);
              setWordData(newData);

              const data = newData.filter((value) => value.sn[0] === e.sn[0]);
              const postParam: IWbwRequest = {
                book: book,
                para: para,
                sn: e.sn[0],
                channel_id: channelId,
                data: data.map((item) => {
                  return {
                    pali: item.word,
                    real: item.real,
                    id: `${book}-${para}-` + e.sn.join("-"),
                    type: item.type,
                    gramma: item.grammar,
                    mean: item.meaning
                      ? {
                          value: item.meaning.value,
                          status: item.meaning?.status,
                        }
                      : undefined,
                    org: item.factors,
                    om: item.factorMeaning,
                    case: item.case,
                    parent: item.parent,
                    pg: item.grammar2,
                    parent2: item.parent2,
                    rela: item.relation,
                    lock: item.locked,
                    note: item.note,
                    bmt: item.bookMarkText,
                    bmc: item.bookMarkColor,
                    cf: item.confidence,
                  };
                }),
              };
              console.log("wbw post", postParam);
              post<IWbwRequest, IWbwUpdateResponse>(`/v2/wbw`, postParam).then(
                (json) => {
                  if (json.ok) {
                    message.info(e.word.value + " updated");
                  } else {
                    message.error(json.message);
                  }
                }
              );

              if (typeof onChange !== "undefined") {
                onChange(newData);
              }
            }}
            onSplit={(isSplit: boolean) => {
              if (isSplit) {
                //拆分
                const newData: IWbw[] = JSON.parse(JSON.stringify(wordData));
                const children: IWbw[] | undefined = wordData[id].factors?.value
                  .split("+")
                  .map((item, index) => {
                    return {
                      word: { value: item, status: 5 },
                      real: { value: item, status: 5 },
                      book: wordData[id].book,
                      para: wordData[id].para,
                      sn: [...wordData[id].sn, index],
                      confidence: 1,
                    };
                  });
                if (typeof children !== "undefined") {
                  console.log("children", children);
                  newData.splice(id + 1, 0, ...children);
                  console.log("new-data", newData);
                  setWordData(newData);
                }
              } else {
                //合并
              }
            }}
          />
        );
      })}
    </div>
  );
};

interface IWidgetWbwSent {
  props: string;
}
const WbwSentWidget = ({ props }: IWidgetWbwSent) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  return <WbwSentCtl {...prop} />;
};

export default WbwSentWidget;

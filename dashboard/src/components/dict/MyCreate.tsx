import { Button, Col, Divider, Input, message, notification, Row } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { SaveOutlined } from "@ant-design/icons";

import WbwDetailBasic from "../template/Wbw/WbwDetailBasic";
import WbwDetailNote from "../template/Wbw/WbwDetailNote";
import { IWbw, IWbwField, TFieldName } from "../template/Wbw/WbwWord";
import { get, post } from "../../request";
import {
  IApiResponseDictList,
  IDictRequest,
  IDictResponse,
  IUserDictCreate,
} from "../api/Dict";
import { useAppSelector } from "../../hooks";
import { add, updateIndex, wordIndex } from "../../reducers/inline-dict";
import store from "../../store";
import { get as getUiLang } from "../../locales";
import { myDictDirty } from "../../reducers/command";
import { currentUser } from "../../reducers/current-user";

export const UserWbwPost = (data: IDictRequest[], view: string) => {
  let wordData: IDictRequest[] = data;
  data.forEach((value: IDictRequest) => {
    if (value.parent && value.type !== "") {
      if (!value.type?.includes("base") && value.type !== ".ind.") {
        let pFactors = "";
        let pFm;
        const orgFactors = value.factors?.split("+");
        if (
          orgFactors &&
          orgFactors.length > 0 &&
          orgFactors[orgFactors.length - 1].includes("[")
        ) {
          pFactors = orgFactors.slice(0, -1).join("+");
          pFm = value.factormean
            ?.split("+")
            .slice(0, orgFactors.length - 1)
            .join("+");
        }
        let grammar = value.grammar?.split("$").slice(0, 1).join("");
        if (value.type?.includes(".v")) {
          grammar = "";
        }
        wordData.push({
          word: value.parent,
          type: "." + value.type?.replaceAll(".", "") + ":base.",
          grammar: grammar,
          mean: value.mean,
          parent: value.parent2 ?? undefined,
          factors: pFactors,
          factormean: pFm,
          confidence: value.confidence,
          language: value.language,
          status: value.status,
        });
      }
    }

    if (value.factors && value.factors.split("+").length > 0) {
      const fm = value.factormean?.split("+");
      const factors: IDictRequest[] = [];
      value.factors.split("+").forEach((factor: string, index: number) => {
        const currWord = factor.replaceAll("-", "");
        console.debug("currWord", currWord);
        const meaning = fm ? fm[index].replaceAll("-", "") ?? null : null;
        if (meaning) {
          factors.push({
            word: currWord,
            type: ".part.",
            grammar: "",
            mean: meaning,
            confidence: value.confidence,
            language: value.language,
            status: value.status,
          });
        }

        const subFactorsMeaning: string[] = fm ? fm[index].split("-") : [];
        factor.split("-").forEach((subFactor, index1) => {
          if (subFactorsMeaning[index1] && subFactorsMeaning[index1] !== "") {
            factors.push({
              word: subFactor,
              type: ".part.",
              grammar: "",
              mean: subFactorsMeaning[index1],
              confidence: value.confidence,
              language: value.language,
              status: value.status,
            });
          }
        });
      });
      wordData = [...wordData, ...factors];
    }
  });
  return post<IUserDictCreate, IDictResponse>("/v2/userdict", {
    view: view,
    data: JSON.stringify(wordData),
  });
};

interface IWidget {
  word?: string;
  onSave?: Function;
}
const MyCreateWidget = ({ word, onSave }: IWidget) => {
  const intl = useIntl();
  const [dirty, setDirty] = useState(false);
  const [wordSpell, setWordSpell] = useState(word);
  const [editWord, setEditWord] = useState<IWbw>({
    word: { value: word ? word : "", status: 7 },
    real: { value: word ? word : "", status: 7 },
    book: 0,
    para: 0,
    sn: [0],
    confidence: 100,
  });
  const [loading, setLoading] = useState(false);
  const inlineWordIndex = useAppSelector(wordIndex);
  const user = useAppSelector(currentUser);

  useEffect(() => setWordSpell(word), [word]);

  useEffect(() => {
    //查询这个词在内存字典里是否有
    if (typeof wordSpell === "undefined") {
      return;
    }
    if (inlineWordIndex.includes(wordSpell)) {
      //已经有了，退出
      return;
    }
    get<IApiResponseDictList>(`/v2/wbwlookup?word=${wordSpell}`).then(
      (json) => {
        console.log("lookup ok", json.data.count);
        //存储到redux
        store.dispatch(add(json.data.rows));
        store.dispatch(updateIndex([wordSpell]));
      }
    );
  }, [inlineWordIndex, wordSpell]);

  const setDataDirty = (dirty: boolean) => {
    store.dispatch(myDictDirty(dirty));
    setDirty(dirty);
  };

  function fieldChanged(field: TFieldName, value: string) {
    let mData: IWbw = JSON.parse(JSON.stringify(editWord));
    switch (field) {
      case "note":
        mData.note = { value: value, status: 7 };
        break;
      case "word":
        mData.word = { value: value, status: 7 };
        break;
      case "real":
        mData.real = { value: value, status: 7 };
        break;
      case "meaning":
        mData.meaning = { value: value, status: 7 };
        break;
      case "factors":
        mData.factors = { value: value, status: 7 };
        break;
      case "factorMeaning":
        mData.factorMeaning = { value: value, status: 7 };
        break;
      case "parent":
        mData.parent = { value: value, status: 7 };
        break;
      case "case":
        console.log("case", value);
        const _case = value.replaceAll("#", "$").split("$");
        const _type = _case[0];
        const _grammar = _case.slice(1).join("$");
        mData.type = { value: _type, status: 7 };
        mData.grammar = { value: _grammar, status: 7 };
        mData.case = { value: value, status: 7 };
        break;
      case "confidence":
        mData.confidence = parseFloat(value);
        break;
      default:
        break;
    }
    console.debug("field changed", mData);
    setEditWord(mData);
    setDataDirty(true);
  }

  const reset = () => {
    let mData: IWbw = JSON.parse(JSON.stringify(editWord));
    mData.note = { value: "", status: 7 };
    mData.meaning = { value: "", status: 7 };
    mData.type = { value: "", status: 7 };
    mData.grammar = { value: "", status: 7 };
    mData.factors = { value: "", status: 7 };
    mData.factorMeaning = { value: "", status: 7 };
    setEditWord(mData);
  };
  return (
    <div style={{ padding: "0 5px" }}>
      <Row>
        <Col
          span={4}
          style={{
            display: "inline-block",
            flexGrow: 0,
            overflow: "hidden",
            whiteSpace: "nowrap",
            textAlign: "right",
            verticalAlign: "middle",
            padding: 5,
          }}
        >
          拼写
        </Col>
        <Col span={20}>
          <Input
            value={wordSpell}
            placeholder="Basic usage"
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              console.debug("spell onChange", event.target.value);
              setWordSpell(event.target.value);
              fieldChanged("word", event.target.value);
            }}
          />
        </Col>
      </Row>

      <WbwDetailBasic
        data={editWord}
        showRelation={false}
        onChange={(e: IWbwField) => {
          console.log("WbwDetailBasic onchange", e);
          fieldChanged(e.field, e.value);
        }}
      />
      <Divider>{intl.formatMessage({ id: "buttons.note" })}</Divider>
      <WbwDetailNote
        data={editWord}
        onChange={(e: IWbwField) => {
          fieldChanged(e.field, e.value);
        }}
      />
      <Divider></Divider>
      <div
        style={{ display: "flex", justifyContent: "space-between", padding: 5 }}
      >
        <Button
          onClick={() => {
            reset();
            setDataDirty(false);
          }}
        >
          重置
        </Button>
        <Button
          loading={loading}
          icon={<SaveOutlined />}
          disabled={!dirty}
          onClick={() => {
            setLoading(true);
            const data: IDictRequest[] = [
              {
                word: editWord.word.value,
                type: editWord.type?.value,
                grammar: editWord.grammar?.value,
                mean: editWord.meaning?.value,
                parent: editWord.parent?.value,
                note: editWord.note?.value,
                factors: editWord.factors?.value,
                factormean: editWord.factorMeaning?.value,
                language: getUiLang(),
                status: user?.roles?.includes("basic") ? 5 : 30,
                confidence: 100,
              },
            ];
            UserWbwPost(data, "dict")
              .finally(() => {
                setLoading(false);
              })
              .then((json) => {
                if (json.ok) {
                  setDataDirty(false);
                  reset();
                  notification.info({
                    message: intl.formatMessage({ id: "flashes.success" }),
                  });

                  if (typeof onSave !== "undefined") {
                    onSave();
                  }
                } else {
                  message.error(json.message);
                }
              });
          }}
          type="primary"
        >
          {intl.formatMessage({ id: "buttons.save" })}
        </Button>
      </div>
    </div>
  );
};

export default MyCreateWidget;

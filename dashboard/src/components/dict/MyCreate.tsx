import { Button, Col, Divider, Input, message, Row } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { SaveOutlined } from "@ant-design/icons";

import WbwDetailBasic from "../template/Wbw/WbwDetailBasic";
import WbwDetailNote from "../template/Wbw/WbwDetailNote";
import { IWbw, IWbwField, TFieldName } from "../template/Wbw/WbwWord";
import { get, post } from "../../request";
import {
  IApiResponseDictList,
  IDictResponse,
  IUserDictCreate,
} from "../api/Dict";
import { useAppSelector } from "../../hooks";
import { add, updateIndex, wordIndex } from "../../reducers/inline-dict";
import store from "../../store";

interface IWidget {
  word?: string;
}
const MyCreateWidget = ({ word }: IWidget) => {
  const intl = useIntl();
  const [wordSpell, setWordSpell] = useState(word);
  const [editWord, setEditWord] = useState<IWbw>({
    word: { value: word ? word : "", status: 1 },
    real: { value: word ? word : "", status: 1 },
    book: 0,
    para: 0,
    sn: [0],
    confidence: 100,
  });
  const [loading, setLoading] = useState(false);
  const inlineWordIndex = useAppSelector(wordIndex);

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

  function fieldChanged(field: TFieldName, value: string) {
    let mData: IWbw = JSON.parse(JSON.stringify(editWord));
    switch (field) {
      case "note":
        mData.note = { value: value, status: 7 };
        break;
      case "word":
        mData.word = { value: value, status: 7 };
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
    setEditWord(mData);
  }
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
              setWordSpell(event.target.value);
              fieldChanged("word", event.target.value);
              fieldChanged("real", event.target.value);
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
        <Button>重置</Button>
        <Button
          loading={loading}
          icon={<SaveOutlined />}
          onClick={() => {
            setLoading(true);
            const data = [
              {
                word: editWord.word.value,
                type: editWord.type?.value,
                grammar: editWord.grammar?.value,
                mean: editWord.meaning?.value,
                parent: editWord.parent?.value,
                note: editWord.note?.value,
                factors: editWord.factors?.value,
                factormean: editWord.factorMeaning?.value,
                confidence: editWord.confidence,
              },
            ];
            post<IUserDictCreate, IDictResponse>("/v2/userdict", {
              view: "dict",
              data: JSON.stringify(data),
            })
              .finally(() => {
                setLoading(false);
              })
              .then((json) => {
                if (json.ok) {
                  message.success(
                    intl.formatMessage({ id: "flashes.success" })
                  );
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

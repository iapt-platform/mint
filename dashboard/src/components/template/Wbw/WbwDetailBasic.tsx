import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Form, Input, AutoComplete, Button, Popover, Space, Badge } from "antd";
import { Collapse } from "antd";
import { MoreOutlined } from "@ant-design/icons";

import SelectCase from "../../dict/SelectCase";
import { IWbw, IWbwField } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import { IApiResponseDictData } from "../../api/Dict";
import WbwDetailFm from "./WbwDetailFm";
import WbwDetailParent2 from "./WbwDetailParent2";
import WbwDetailRelation from "./WbwDetailRelation";
import WbwDetailFactor from "./WbwDetailFactor";

const { Panel } = Collapse;

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}

export interface IWordBasic {
  meaning?: string[];
  case?: string;
  factors?: string;
  factorMeaning?: string;
  parent?: string;
}
export const getParentInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[]
): string[] => {
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    let myMap = new Map<string, number>();
    let parent: string[] = [];
    for (const iterator of result) {
      myMap.set(iterator.parent, 1);
    }
    myMap.forEach((value, key, map) => {
      parent.push(key);
    });
    return parent;
  } else {
    return [];
  }
};

interface IWidget {
  data: IWbw;
  showRelation?: boolean;
  onChange?: Function;
  onRelationAdd?: Function;
}
const WbwDetailBasicWidget = ({
  data,
  showRelation = true,
  onChange,
  onRelationAdd,
}: IWidget) => {
  const [form] = Form.useForm();
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);
  const [parentOptions, setParentOptions] = useState<ValueType[]>([]);
  const [factors, setFactors] = useState<string[] | undefined>(
    data.factors?.value?.split("+")
  );
  const [openCreate, setOpenCreate] = useState(false);
  const [_meaning, setMeaning] = useState<string | undefined>(
    data.meaning?.value
  );
  useEffect(() => {
    setMeaning(data.meaning?.value);
  }, [data.meaning]);
  const onMeaningChange = (value: string) => {
    console.log(`Selected: ${value}`);
    if (typeof onChange !== "undefined") {
      onChange({ field: "meaning", value: value });
    }
  };

  useEffect(() => {
    const parent = getParentInDict(
      data.word.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );
    const parentOptions = parent.map((item) => {
      return {
        label: item,
        value: item,
      };
    });
    setParentOptions(parentOptions);
  }, [inlineDict, data]);

  const relationCount = data.relation
    ? JSON.parse(data.relation.value).length
    : 0;

  return (
    <>
      <Form
        labelCol={{ span: 4 }}
        wrapperCol={{ span: 20 }}
        className="wbw_detail_basic"
        name="basic"
        form={form}
        initialValues={{
          meaning: data.meaning?.value,
          factors: data.factors?.value,
          factorMeaning: data.factorMeaning?.value,
          parent: data.parent?.value,
          case: data.case?.value,
          parent2: data.parent2?.value,
          grammar2: data.grammar2?.value,
        }}
      >
        <Form.Item
          style={{ marginBottom: 6 }}
          name="meaning"
          label={intl.formatMessage({ id: "forms.fields.meaning.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.meaning.tooltip" })}
        >
          <div style={{ display: "flex" }}>
            <Input
              value={_meaning}
              allowClear
              onChange={(e) => {
                console.log("meaning input", e.target.value);
                setMeaning(e.target.value);
                onMeaningChange(e.target.value);
              }}
            />
            <Popover
              content={
                <WbwMeaningSelect
                  data={data}
                  onSelect={(meaning: string) => {
                    console.log(meaning);
                    setMeaning(meaning);
                    form.setFieldsValue({
                      meaning: meaning,
                    });
                    onMeaningChange(meaning);
                  }}
                />
              }
              overlayStyle={{ width: 500 }}
              placement="bottom"
              trigger="click"
              open={openCreate}
              onOpenChange={(open: boolean) => {
                setOpenCreate(open);
              }}
            >
              <Button type="text" icon={<MoreOutlined />} onClick={() => {}} />
            </Popover>
          </div>
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          name="factors"
          label={intl.formatMessage({ id: "forms.fields.factors.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.factors.tooltip" })}
        >
          <WbwDetailFactor
            data={data}
            onChange={(value: string) => {
              setFactors(value.split("+"));
              if (typeof onChange !== "undefined") {
                onChange({ field: "factors", value: value });
              }
            }}
          />
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          name="factorMeaning"
          label={intl.formatMessage({
            id: "forms.fields.factor.meaning.label",
          })}
          tooltip={intl.formatMessage({
            id: "forms.fields.factor.meaning.tooltip",
          })}
        >
          <WbwDetailFm
            factors={factors}
            initValue={data.factorMeaning?.value?.split("+")}
            onChange={(value: string[]) => {
              console.log("fm change", value);
              if (typeof onChange !== "undefined") {
                onChange({ field: "factorMeaning", value: value.join("+") });
              }
            }}
            onJoin={(value: string) => {
              setMeaning(value);
              onMeaningChange(value);
            }}
          />
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
          name="case"
        >
          <SelectCase
            onCaseChange={(value: string) => {
              if (typeof onChange !== "undefined") {
                onChange({ field: "case", value: value });
              }
            }}
          />
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          name="parent"
          label={intl.formatMessage({
            id: "forms.fields.parent.label",
          })}
          tooltip={intl.formatMessage({
            id: "forms.fields.parent.tooltip",
          })}
        >
          <AutoComplete
            options={parentOptions}
            onChange={(value: any, option: ValueType | ValueType[]) => {
              if (typeof onChange !== "undefined") {
                onChange({ field: "parent", value: value });
              }
            }}
          >
            <Input allowClear />
          </AutoComplete>
        </Form.Item>
        <Collapse bordered={false}>
          <Panel header="词源" key="parent2">
            <WbwDetailParent2
              data={data}
              onChange={(e: IWbwField) => {
                if (typeof onChange !== "undefined") {
                  onChange(e);
                }
              }}
            />
          </Panel>
          <Panel
            header={
              <Space>
                {"关联"}
                <Badge color="geekblue" count={relationCount} />
              </Space>
            }
            key="relation"
            style={{ display: showRelation ? "block" : "none" }}
          >
            <WbwDetailRelation
              data={data}
              onChange={(e: IWbwField) => {
                if (typeof onChange !== "undefined") {
                  onChange(e);
                }
              }}
              onAdd={() => {
                if (typeof onRelationAdd !== "undefined") {
                  onRelationAdd();
                }
              }}
            />
          </Panel>
        </Collapse>
      </Form>
    </>
  );
};

export default WbwDetailBasicWidget;

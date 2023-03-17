import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Divider, Form, Select, Input, AutoComplete } from "antd";
import { Collapse } from "antd";

import SelectCase from "../../dict/SelectCase";
import { IWbw } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import { getFactorsInDict } from "./WbwFactors";

const { Option } = Select;
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

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  const [form] = Form.useForm();
  const intl = useIntl();
  const [items, setItems] = useState<string[]>([]);
  const inlineDict = useAppSelector(_inlineDict);
  const [factorOptions, setFactorOptions] = useState<ValueType[]>([]);
  const onMeaningChange = (value: string | string[]) => {
    console.log(`Selected: ${value}`);
    if (typeof onChange !== "undefined") {
      if (typeof value === "string") {
        onChange({ field: "meaning", value: value });
      } else {
        onChange({ field: "meaning", value: value.join("$") });
      }
    }
  };

  useEffect(() => {
    const factors = getFactorsInDict(
      data.word.value,
      inlineDict.wordIndex,
      inlineDict.wordList
    );
    const options = factors.map((item) => {
      return {
        label: item,
        value: item,
      };
    });
    setFactorOptions(options);
  }, [inlineDict, data]);

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
        }}
      >
        <Form.Item
          style={{ marginBottom: 6 }}
          name="meaning"
          label={intl.formatMessage({ id: "forms.fields.meaning.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.meaning.tooltip" })}
        >
          <Select
            allowClear
            mode="tags"
            onChange={onMeaningChange}
            style={{ width: "100%" }}
            placeholder={intl.formatMessage({
              id: "forms.fields.meaning.label",
            })}
            options={items.map((item) => ({ label: item, value: item }))}
            dropdownRender={(menu) => (
              <>
                {menu}
                <Divider style={{ margin: "8px 0" }}>更多</Divider>
                <WbwMeaningSelect
                  data={data}
                  onSelect={(meaning: string) => {
                    const currMeanings = form.getFieldValue("meaning") || [];
                    console.log(meaning);
                    if (!items.includes(meaning)) {
                      setItems([...items, meaning]);
                    }
                    if (!currMeanings.includes(meaning)) {
                      currMeanings.push(meaning);
                      console.log("it push", meaning);
                    }
                    form.setFieldsValue({
                      meaning: currMeanings,
                    });
                    onMeaningChange(currMeanings);
                  }}
                />
              </>
            )}
          />
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          name="factors"
          label={intl.formatMessage({ id: "forms.fields.factors.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.factors.tooltip" })}
        >
          <AutoComplete options={factorOptions}>
            <Input
              allowClear
              placeholder={intl.formatMessage({
                id: "forms.fields.factors.label",
              })}
            />
          </AutoComplete>
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
          <Input
            allowClear
            placeholder={intl.formatMessage({
              id: "forms.fields.factor.meaning.label",
            })}
          />
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
          name="case"
        >
          <SelectCase
            onCaseChange={(value: (string | number)[]) => {
              if (typeof onChange !== "undefined") {
                onChange({ field: "case", value: value.join("$") });
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
          <Input
            allowClear
            placeholder={intl.formatMessage({
              id: "forms.fields.parent.label",
            })}
          />
        </Form.Item>
        <Collapse bordered={false}>
          <Panel header="词源" key="1">
            <Form.Item
              name="parent1"
              label={intl.formatMessage({ id: "forms.fields.parent.label" })}
              tooltip={intl.formatMessage({
                id: "forms.fields.parent.tooltip",
              })}
            >
              <Input
                allowClear
                placeholder={intl.formatMessage({
                  id: "forms.fields.parent.label",
                })}
                addonAfter={
                  <Form.Item name="suffix" noStyle>
                    <Select style={{ width: 100 }} allowClear>
                      <Option value="prp">现在分词</Option>
                      <Option value="pp">过去分词</Option>
                      <Option value="fpp">未来分词</Option>
                    </Select>
                  </Form.Item>
                }
              />
            </Form.Item>
          </Panel>
          <Panel header="关系" key="2">
            关系语法
          </Panel>
        </Collapse>
      </Form>
    </>
  );
};

export default Widget;

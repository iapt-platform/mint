import { useState } from "react";
import { useIntl } from "react-intl";
import { Divider, Form, Select, Input, Cascader } from "antd";
import { Collapse } from "antd";

import SelectCase from "../../dict/SelectCase";
import { IWbw } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";

const { Option } = Select;
const { Panel } = Collapse;

export interface IWordBasic {
  meaning?: string[];
  case?: string;
  factors?: string;
  factorMeaning?: string;
  parent?: string;
}

interface CascaderOption {
  value: string | number;
  label: string;
  children?: CascaderOption[];
}

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  const [form] = Form.useForm();
  const intl = useIntl();
  const [items, setItems] = useState(["jack", "lucy"]);

  const formItemLayout = {
    labelCol: { span: 4 },
    wrapperCol: { span: 20 },
  };
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

  const options: CascaderOption[] = [
    {
      value: "n",
      label: intl.formatMessage({ id: "dict.fields.type.n.label" }),
    },
    {
      value: "ti",
      label: intl.formatMessage({ id: "dict.fields.type.ti.label" }),
    },
    {
      value: "v",
      label: intl.formatMessage({ id: "dict.fields.type.v.label" }),
    },
    {
      value: "ind",
      label: intl.formatMessage({ id: "dict.fields.type.ind.label" }),
    },
    {
      value: "un",
      label: intl.formatMessage({ id: "dict.fields.type.un.label" }),
    },
    {
      value: "adj",
      label: intl.formatMessage({ id: "dict.fields.type.adj.label" }),
    },
  ];

  return (
    <>
      <Form
        {...formItemLayout}
        className="wbw_detail_basic"
        name="basic"
        form={form}
        initialValues={{
          meaning: data.meaning?.value,
          factors: data.factors?.value,
          factorMeaning: data.factorMeaning?.value,
          parent: data.parent?.value,
          case: data.case?.value,
          case1: data.case?.value,
        }}
      >
        <Form.Item
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
          name="factors"
          label={intl.formatMessage({ id: "forms.fields.factors.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.factors.tooltip" })}
        >
          <Input
            allowClear
            placeholder={intl.formatMessage({
              id: "forms.fields.factors.label",
            })}
          />
        </Form.Item>
        <Form.Item
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
          name="case"
        >
          <Cascader options={options} placeholder="Please select case" />
        </Form.Item>
        <Form.Item
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
          name="case1"
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

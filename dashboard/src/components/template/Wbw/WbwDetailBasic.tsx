import { useRef, useState, useEffect } from "react";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormSelect,
  ProFormInstance,
} from "@ant-design/pro-components";
import { Divider, message, Form, Select } from "antd";
import { Collapse, Tag } from "antd";

import SelectCase from "../../studio/SelectCase";
import { IWbw } from "./WbwWord";

const { Panel } = Collapse;

const handleChange = (value: string | string[]) => {
  console.log(`Selected: ${value}`);
};

export interface IWordBasic {
  meaning?: string[];
  case?: string;
  factors?: string;
  factorMeaning?: string;
  parent?: string;
}
interface IWidget {
  data: IWbw;
  submit?: boolean;
  onSubmit?: Function;
}
const Widget = ({ data, submit = false, onSubmit }: IWidget) => {
  const formRef = useRef<ProFormInstance>();
  const intl = useIntl();
  const [items, setItems] = useState(["jack", "lucy"]);
  const formItemLayout = {
    labelCol: { span: 4 },
    wrapperCol: { span: 14 },
  };
  useEffect(() => {
    if (submit) {
      if (typeof onSubmit !== "undefined") {
        onSubmit(formRef.current?.getFieldFormatValue?.());
      }
    }
  }, [submit]);
  return (
    <ProForm<IWordBasic>
      formRef={formRef}
      {...formItemLayout}
      layout="horizontal"
      submitter={{
        render: (props, doms) => {
          return <></>;
        },
      }}
      onFinish={async (values) => {
        console.log(values);
        message.success("提交成功");
      }}
      params={{}}
      request={async () => {
        return {
          meaning: data.meaning?.value,
          factors: data.factors?.value,
          parent: data.parent?.value,
          case: data.type?.value,
        };
      }}
    >
      <Select
        mode="tags"
        placeholder="Please select"
        defaultValue={data.meaning?.value}
        onChange={handleChange}
        style={{ width: "100%" }}
        options={items.map((item) => ({ label: item, value: item }))}
      />
      <ProFormSelect
        width="md"
        name="meaning"
        dependencies={["meaning"]}
        label={intl.formatMessage({ id: "forms.fields.meaning.label" })}
        tooltip={intl.formatMessage({ id: "forms.fields.meaning.tooltip" })}
        placeholder={intl.formatMessage({ id: "forms.fields.meaning.label" })}
        options={items.map((item) => ({ label: item, value: item }))}
        fieldProps={{
          mode: "tags",
          optionItemRender(item) {
            return item.label + " - " + item.value;
          },
          dropdownRender(menu) {
            return (
              <>
                {menu}
                <Divider style={{ margin: "8px 0" }}>更多</Divider>
                <Collapse defaultActiveKey={["1"]}>
                  <Panel
                    header="This is panel header 1"
                    style={{ padding: 0 }}
                    key="1"
                  >
                    <Tag
                      onClick={(e: React.MouseEvent<HTMLAnchorElement>) => {
                        e.preventDefault();

                        const it =
                          formRef.current?.getFieldValue("meaning") || [];
                        console.log(it);
                        if (!items.includes("hello")) {
                          setItems([...items, "hello"]);
                        }
                        if (!it.includes("hello")) {
                          it.push("hello");
                          console.log("it push", it);
                        }
                        formRef.current?.setFieldsValue({ meaning: it });
                      }}
                    >
                      意思
                    </Tag>
                  </Panel>
                  <Panel header="This is panel header 2" key="2">
                    <Tag>意思</Tag>
                  </Panel>
                  <Panel header="This is panel header 3" key="3">
                    <Tag>意思</Tag>
                  </Panel>
                </Collapse>
              </>
            );
          },
        }}
      />
      <ProFormText
        width="md"
        name="factors"
        label={intl.formatMessage({ id: "forms.fields.factors.label" })}
        tooltip={intl.formatMessage({ id: "forms.fields.factors.tooltip" })}
        placeholder={intl.formatMessage({ id: "forms.fields.factors.label" })}
      />
      <Form.Item
        label={intl.formatMessage({ id: "forms.fields.case.label" })}
        tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
        name="case"
      >
        <SelectCase />
      </Form.Item>
      <ProFormText
        name="parent"
        width="md"
        label={intl.formatMessage({ id: "forms.fields.parent.label" })}
        tooltip={intl.formatMessage({ id: "forms.fields.parent.tooltip" })}
        placeholder={intl.formatMessage({ id: "forms.fields.parent.label" })}
      />
      <Collapse bordered={false}>
        <Panel header="词源" key="1">
          <ProFormText
            name="parent1"
            width="md"
            label={intl.formatMessage({ id: "forms.fields.parent.label" })}
            tooltip={intl.formatMessage({ id: "forms.fields.parent.tooltip" })}
            placeholder={intl.formatMessage({
              id: "forms.fields.parent.label",
            })}
          />
          <ProFormSelect
            width="md"
            name="grammar"
            label={intl.formatMessage({ id: "forms.fields.meaning.label" })}
            tooltip={intl.formatMessage({ id: "forms.fields.meaning.tooltip" })}
            placeholder={intl.formatMessage({
              id: "forms.fields.meaning.label",
            })}
            options={[
              { label: "过去分词", value: "pp" },
              { label: "现在分词", value: "prp" },
              { label: "未来分词", value: "fpp" },
            ]}
            fieldProps={{
              optionItemRender(item) {
                return item.label + " - " + item.value;
              },
            }}
          />
        </Panel>
        <Panel header="关系" key="2">
          关系语法
        </Panel>
      </Collapse>
    </ProForm>
  );
};

export default Widget;

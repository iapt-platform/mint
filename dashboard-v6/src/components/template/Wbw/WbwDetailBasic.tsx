import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Form, Input, Button, Popover } from "antd";
import { Collapse } from "antd";
import { MoreOutlined } from "@ant-design/icons";

import { IWbw, IWbwField } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";

import WbwDetailFm from "./WbwDetailFm";
import WbwDetailParent2 from "./WbwDetailParent2";
import WbwDetailFactor from "./WbwDetailFactor";
import WbwDetailBasicRelation from "./WbwDetailBasicRelation";
import WbwDetailParent from "./WbwDetailParent";
import WbwDetailCase from "./WbwDetailCase";
import WbwDetailOrder from "./WbwDetailOrder";

const { Panel } = Collapse;

export interface IWordBasic {
  meaning?: string[];
  case?: string;
  factors?: string;
  factorMeaning?: string;
  parent?: string;
}

interface IWidget {
  data: IWbw;
  visible?: boolean;
  showRelation?: boolean;
  readonly?: boolean;
  onChange?: Function;
  onRelationAdd?: Function;
}
const WbwDetailBasicWidget = ({
  data,
  visible,
  showRelation = true,
  readonly = false,
  onChange,
  onRelationAdd,
}: IWidget) => {
  const [form] = Form.useForm();
  const intl = useIntl();
  const [factors, setFactors] = useState<string[] | undefined>(
    data.factors?.value?.split("+")
  );
  const [openCreate, setOpenCreate] = useState(false);
  const [_meaning, setMeaning] = useState<string | undefined>();
  const [currTip, setCurrTip] = useState(1);

  useEffect(() => {
    if (typeof data.meaning?.value === "string") {
      setMeaning(data.meaning?.value);
    }
  }, [data.meaning]);
  const onMeaningChange = (value: string) => {
    console.log(`Selected: ${value}`);
    if (typeof onChange !== "undefined") {
      onChange({ field: "meaning", value: value });
    }
  };

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
            <div style={{ display: "flex", width: "100%" }}>
              <Input
                disabled={readonly}
                value={_meaning}
                allowClear
                placeholder="请输入"
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
                trigger={readonly ? "" : "click"}
                overlayStyle={{ width: 500 }}
                placement="bottom"
                open={openCreate}
                onOpenChange={(open: boolean) => {
                  setOpenCreate(open);
                }}
              >
                <Button
                  disabled={readonly}
                  type="text"
                  icon={<MoreOutlined />}
                />
              </Popover>
            </div>
            <WbwDetailOrder
              sn={5}
              visible={visible}
              curr={currTip}
              onChange={() => setCurrTip(currTip + 1)}
            />
          </div>
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          name="factors"
          label={intl.formatMessage({ id: "forms.fields.factors.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.factors.tooltip" })}
        >
          <div style={{ display: "flex" }}>
            <WbwDetailFactor
              readonly={readonly}
              data={data}
              onChange={(value: string) => {
                setFactors(value.split("+"));
                if (typeof onChange !== "undefined") {
                  onChange({ field: "factors", value: value });
                }
              }}
            />
            <WbwDetailOrder
              sn={2}
              visible={visible}
              curr={currTip}
              onChange={() => setCurrTip(currTip + 1)}
            />
          </div>
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
          <div style={{ display: "flex" }}>
            <WbwDetailFm
              factors={factors}
              readonly={readonly}
              value={data.factorMeaning?.value?.split("+")}
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
            <WbwDetailOrder
              sn={4}
              visible={visible}
              curr={currTip}
              onChange={() => setCurrTip(currTip + 1)}
            />
          </div>
        </Form.Item>
        <Form.Item
          style={{ marginBottom: 6 }}
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
          tooltip={intl.formatMessage({ id: "forms.fields.case.tooltip" })}
          name="case"
        >
          <div style={{ display: "flex" }}>
            <WbwDetailCase
              readonly={readonly}
              data={data}
              onChange={(value: string) => {
                if (typeof onChange !== "undefined") {
                  onChange({ field: "case", value: value });
                }
              }}
            />
            <WbwDetailOrder
              sn={3}
              visible={visible}
              curr={currTip}
              onChange={() => setCurrTip(currTip + 1)}
            />
          </div>
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
          <div style={{ display: "flex" }}>
            <WbwDetailParent
              readonly={readonly}
              data={data}
              onChange={(value: string) => {
                if (typeof onChange !== "undefined") {
                  onChange({ field: "parent", value: value });
                }
              }}
            />
            <WbwDetailOrder
              sn={1}
              visible={visible}
              curr={currTip}
              onChange={() => setCurrTip(currTip + 1)}
            />
          </div>
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
        </Collapse>
        <WbwDetailBasicRelation
          data={data}
          showRelation={showRelation}
          onChange={(e: IWbwField) => {
            if (typeof onChange !== "undefined") {
              onChange(e);
            }
          }}
          onRelationAdd={onRelationAdd}
        />
      </Form>
    </>
  );
};

export default WbwDetailBasicWidget;

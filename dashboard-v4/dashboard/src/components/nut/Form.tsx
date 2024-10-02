import { useRef } from "react";
import { ProForm, ProFormSelect } from "@ant-design/pro-components";
import type { ProFormInstance } from "@ant-design/pro-components";
import { Button } from "antd";

interface IFormData {
  sv: number;
}

const Widget = () => {
  const formRef = useRef<ProFormInstance>();
  const svCur = 5;
  const onWhat = () => {
    const it = formRef.current?.getFieldValue("sv") || [];
    console.log(it);

    if (!it.includes(svCur)) {
      it.push(svCur);
    }

    formRef.current?.setFieldsValue({ sv: it });
  };
  const onReset = () => {
    formRef.current?.resetFields();
  };
  return (
    <ProForm<IFormData>
      name="demo"
      formRef={formRef}
      submitter={{
        render: (props, doms) => {
          return [
            ...doms,
            <Button.Group key="refs" style={{ display: "block" }}>
              <Button htmlType="button" onClick={onWhat} key="what">
                What?
              </Button>
              <Button htmlType="button" onClick={onReset} key="reset">
                Reset
              </Button>
            </Button.Group>,
          ];
        },
      }}
    >
      <ProFormSelect
        width="md"
        name="sv"
        mode="multiple"
        allowClear
        dependencies={["sv"]}
        options={Array.from(Array(10).keys()).map((x) => {
          return { value: x, label: `V${x}`, disabled: x === svCur };
        })}
      />
    </ProForm>
  );
};

export default Widget;

import { useIntl } from "react-intl";
import { ProForm } from "@ant-design/pro-components";
import { message } from "antd";

import DictEditInner from "./DictEditInner";

export interface IDictFormData {
  id: number;
  word: string;
  type?: string | null;
  grammar?: string | null;
  parent?: string | null;
  meaning?: string | null;
  note?: string | null;
  factors?: string | null;
  factormeaning?: string | null;
  lang: string;
  confidence: number;
}

type IWidgetDictCreate = {
  studio: string;
  word?: string;
};
const DictCreateWidget = (prop: IWidgetDictCreate) => {
  const intl = useIntl();

  return (
    <>
      <ProForm<IDictFormData>
        onFinish={async (values: IDictFormData) => {
          // TODO 是否要删掉？
          console.log(values);
          message.success(intl.formatMessage({ id: "flashes.success" }));
        }}
      >
        <DictEditInner word={prop.word} />
      </ProForm>
    </>
  );
};

export default DictCreateWidget;

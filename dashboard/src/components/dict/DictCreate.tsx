import { useIntl } from "react-intl";
import { ProForm } from "@ant-design/pro-components";
import { message } from "antd";

import DictEditInner from "./DictEditInner";

export interface IDictFormData {
  id: number;
  word: string;
  type: string;
  grammar: string;
  parent: string;
  meaning: string;
  note: string;
  factors: string;
  factormeaning: string;
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
          // TODO
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

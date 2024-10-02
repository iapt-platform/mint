import { useIntl } from "react-intl";
import { ProForm } from "@ant-design/pro-components";
import { message } from "antd";

import { IApiResponseDict, IDictRequest } from "../api/Dict";
import { get, put } from "../../request";

import DictEditInner from "./DictEditInner";
import { IDictFormData } from "./DictCreate";

interface IWidget {
  wordId?: string;
}
const DictEditWidget = ({ wordId }: IWidget) => {
  const intl = useIntl();

  return (
    <>
      <ProForm<IDictFormData>
        onFinish={async (values: IDictFormData) => {
          console.log(values);
          const request: IDictRequest = {
            id: values.id,
            word: values.word,
            type: values.type,
            grammar: values.grammar,
            mean: values.meaning,
            parent: values.parent,
            note: values.note,
            factors: values.factors,
            factormean: values.factormeaning,
            language: values.lang,
            confidence: values.confidence,
          };
          const res = await put<IDictRequest, IApiResponseDict>(
            `/v2/userdict/${wordId}`,
            request
          );
          console.log(res);
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
          } else {
            message.success(res.message);
          }
        }}
        formKey="dict_edit"
        request={async () => {
          const res: IApiResponseDict = await get(`/v2/userdict/${wordId}`);
          return {
            id: 1,
            wordId: res.data.id,
            word: res.data.word,
            type: res.data.type,
            grammar: res.data.grammar,
            parent: res.data.parent,
            meaning: res.data.mean,
            note: res.data.note,
            factors: res.data.factors,
            factormeaning: res.data.factormean,
            lang: res.data.language,
            confidence: res.data.confidence,
          };
        }}
      >
        <DictEditInner />
      </ProForm>
    </>
  );
};

export default DictEditWidget;

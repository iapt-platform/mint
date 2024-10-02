import { ModalForm, ProForm, ProFormText } from "@ant-design/pro-components";
import { Form, message } from "antd";

import { useState } from "react";
import { useIntl } from "react-intl";
import {
  INissayaEnding,
  INissayaEndingRequest,
  INissayaEndingResponse,
} from "../../../pages/admin/nissaya-ending/list";
import { get, post, put } from "../../../request";
import LangSelect from "../../general/LangSelect";
import GrammarSelect from "./GrammarSelect";

interface IWidget {
  trigger?: JSX.Element;
  id?: string;
  onSuccess?: Function;
}
const NissayaEndingWidget = ({
  trigger = <>{"trigger"}</>,
  id,
  onSuccess,
}: IWidget) => {
  const [title, setTitle] = useState<string | undefined>(id ? "" : "新建");
  const [form] = Form.useForm<INissayaEnding>();
  const intl = useIntl();
  return (
    <ModalForm<INissayaEnding>
      title={title}
      trigger={trigger}
      form={form}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        let data = values;
        data.from = {
          spell: values.fromSpell,
          case: values.fromCase ? values.fromCase : undefined,
        };
        let res: INissayaEndingResponse;
        if (typeof id === "undefined") {
          res = await post<INissayaEndingRequest, INissayaEndingResponse>(
            `/v2/nissaya-ending`,
            data
          );
        } else {
          res = await put<INissayaEndingRequest, INissayaEndingResponse>(
            `/v2/nissaya-ending/${id}`,
            data
          );
        }
        console.log(res);
        if (res.ok) {
          message.success("提交成功");
          if (typeof onSuccess !== "undefined") {
            onSuccess();
          }
        } else {
          message.error(res.message);
        }

        return true;
      }}
      request={
        id
          ? async () => {
              const res = await get<INissayaEndingResponse>(
                `/v2/nissaya-ending/${id}`
              );
              console.log("nissaya-ending get", res);
              if (res.ok) {
                setTitle(res.data.ending);

                return {
                  id: id,
                  ending: res.data.ending,
                  relation: res.data.relation,
                  from: res.data.from,
                  fromCase: res.data.from?.case,
                  fromSpell: res.data.from?.spell,
                  lang: res.data.lang,
                };
              } else {
                return {
                  id: undefined,
                  ending: "",
                  relation: "",
                  lang: "",
                };
              }
            }
          : undefined
      }
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="ending"
          label={intl.formatMessage({ id: "forms.fields.ending.label" })}
          tooltip="最长为 24 位"
        />
        <LangSelect width="md" />
      </ProForm.Group>
      <ProForm.Group>
        <GrammarSelect name="fromCase" />
        <ProFormText
          width="md"
          name="fromSpell"
          label={intl.formatMessage({ id: "buttons.spell" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="relation"
          label={intl.formatMessage({ id: "forms.fields.relation.label" })}
        />
      </ProForm.Group>
    </ModalForm>
  );
};

export default NissayaEndingWidget;

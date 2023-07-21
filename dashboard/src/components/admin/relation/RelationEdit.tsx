import {
  ModalForm,
  ProForm,
  ProFormDependency,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";
import { Form, message } from "antd";

import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import {
  IRelation,
  IRelationRequest,
  IRelationResponse,
} from "../../../pages/admin/relation/list";
import { get, post, put } from "../../../request";
import GrammarSelect from "./GrammarSelect";

export const _verb = [
  "n",
  "ti",
  "v",
  "v:ind",
  "ind",
  "sg",
  "pl",
  "nom",
  "acc",
  "gen",
  "dat",
  "inst",
  "voc",
  "abl",
  "loc",
  "base",
  "imp",
  "opt",
  "pres",
  "aor",
  "fut",
  "1p",
  "2p",
  "3p",
  "prp",
  "pp",
  "grd",
  "fpp",
  "vdn",
  "ger",
  "inf",
  "adj",
  "pron",
  "caus",
  "num",
  "adv",
  "conj",
  "pre",
  "suf",
  "ti:base",
  "n:base",
  "v:base",
  "vdn",
];
interface IWidget {
  trigger?: JSX.Element;
  id?: string;
  onSuccess?: Function;
}
const RelationEditWidget = ({
  trigger = <>{"trigger"}</>,
  id,
  onSuccess,
}: IWidget) => {
  const [title, setTitle] = useState<string | undefined>(id ? "" : "新建");
  const [form] = Form.useForm<IRelation>();
  const formRef = useRef<ProFormInstance>();
  const intl = useIntl();

  const verbOptions = _verb.map((item) => {
    return {
      value: item,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
      }),
    };
  });
  return (
    <ModalForm<IRelation>
      title={title}
      trigger={trigger}
      formRef={formRef}
      form={form}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("onCancel"),
      }}
      submitTimeout={3000}
      onFinish={async (values) => {
        let data = values;
        data.from = { spell: values.fromSpell, case: values.fromCase };
        let res: IRelationResponse;
        if (typeof id === "undefined") {
          res = await post<IRelationRequest, IRelationResponse>(
            `/v2/relation`,
            data
          );
        } else {
          res = await put<IRelationRequest, IRelationResponse>(
            `/v2/relation/${id}`,
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
              const res = await get<IRelationResponse>(`/v2/relation/${id}`);
              console.log("relation get", res);
              if (res.ok) {
                setTitle(res.data.name);

                return {
                  id: id,
                  name: res.data.name,
                  case: res.data.case,
                  from: res.data.from,
                  fromCase: res.data.from?.case,
                  fromSpell: res.data.from?.spell,
                  to: res.data.to,
                  category: res.data.category,
                };
              } else {
                return {
                  id: undefined,
                  name: "",
                };
              }
            }
          : undefined
      }
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          label={intl.formatMessage({ id: "forms.fields.name.label" })}
        />
      </ProForm.Group>
      <ProForm.Group title="from">
        <GrammarSelect />
        <ProFormText
          width="md"
          name="fromSpell"
          label={intl.formatMessage({ id: "buttons.spell" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormSelect
          options={verbOptions}
          fieldProps={{
            mode: "tags",
          }}
          width="md"
          name="to"
          allowClear={false}
          label={intl.formatMessage({ id: "forms.fields.to.label" })}
        />
        <ProFormText
          width="md"
          name="category"
          label={intl.formatMessage({ id: "forms.fields.category.label" })}
        />
      </ProForm.Group>
    </ModalForm>
  );
};

export default RelationEditWidget;

import {
  ModalForm,
  ProForm,
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

interface IWidget {
  trigger?: JSX.Element;
  id?: string;
  onSuccess?: Function;
}
const Widget = ({ trigger = <>{"trigger"}</>, id, onSuccess }: IWidget) => {
  const [title, setTitle] = useState<string | undefined>(id ? "" : "新建");
  const [form] = Form.useForm<IRelation>();
  const formRef = useRef<ProFormInstance>();
  const intl = useIntl();
  const _case = ["nom", "acc", "gen", "dat", "inst", "abl", "loc"];
  const caseOptions = _case.map((item) => {
    return {
      value: item,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
      }),
    };
  });

  const _verb = ["pass", "v", "caus", "abs"];
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
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        console.log("submit", values);
        let res: IRelationResponse;
        if (typeof id === "undefined") {
          res = await post<IRelationRequest, IRelationResponse>(
            `/v2/relation`,
            values
          );
        } else {
          res = await put<IRelationRequest, IRelationResponse>(
            `/v2/relation/${id}`,
            values
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
                  to: res.data.to,
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
      <ProForm.Group>
        <ProFormSelect
          options={caseOptions}
          fieldProps={{
            mode: "tags",
          }}
          width="md"
          name="case"
          allowClear={false}
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
        />

        <ProFormSelect
          options={verbOptions}
          fieldProps={{
            mode: "tags",
          }}
          width="md"
          name="to"
          allowClear={false}
          label={intl.formatMessage({ id: "forms.fields.case.label" })}
        />
      </ProForm.Group>
    </ModalForm>
  );
};

export default Widget;

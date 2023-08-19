import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";
import { post } from "../../request";
import { IGroupRequest, IGroupResponse } from "../api/Group";
import { useRef } from "react";

interface IFormData {
  name: string;
}

interface IWidgetGroupCreate {
  studio?: string;
  onCreate?: Function;
}
const GroupCreateWidget = ({ studio, onCreate }: IWidgetGroupCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof studio === "undefined") {
          return;
        }
        console.log(values);
        const res = await post<IGroupRequest, IGroupResponse>(`/v2/group`, {
          name: values.name,
          studio_name: studio,
        });
        console.log(res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onCreate !== "undefined") {
            onCreate();
            formRef.current?.resetFields();
          }
        } else {
          message.error(res.message);
        }
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "channel.name" })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "channel.create.message.noname",
              }),
            },
          ]}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default GroupCreateWidget;

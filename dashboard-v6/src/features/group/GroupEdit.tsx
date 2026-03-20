import { useState } from "react";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message, Card } from "antd";

import { get, put } from "../../request";

import type { IGroupRequest, IGroupResponse } from "../../api/group";

interface IFormData {
  id: string;
  name: string;
  description: string;
}

interface IWidget {
  groupId?: string;
}
const GroupEdit = ({ groupId }: IWidget) => {
  const intl = useIntl();

  const [title, setTitle] = useState("Loading");

  return (
    <Card title={title}>
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          console.log(values);
          const res = await put<IGroupRequest, IGroupResponse>(
            `/api/v2/group/${groupId}`,
            values
          );
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
          }
        }}
        formKey="group_edit"
        request={async () => {
          const res = await get<IGroupResponse>(`/api/v2/group/${groupId}`);
          setTitle(res.data.name);
          document.title = `${res.data.name}`;
          return {
            id: res.data.uid,
            name: res.data.name,
            description: res.data.description,
          };
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="name"
            required
            label={intl.formatMessage({ id: "forms.fields.name.label" })}
            rules={[
              {
                required: true,
              },
            ]}
          />
        </ProForm.Group>

        <ProForm.Group>
          <ProFormTextArea
            width="md"
            name="description"
            label={intl.formatMessage({
              id: "forms.fields.description.label",
            })}
          />
        </ProForm.Group>
      </ProForm>
    </Card>
  );
};

export default GroupEdit;

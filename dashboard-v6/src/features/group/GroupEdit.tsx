import { useState } from "react";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormRadio,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message, Card } from "antd";

import {
  fetchGroup,
  GROUP_STATUS_PRIVATE,
  GROUP_STATUS_PUBLIC,
  updateGroup,
} from "../../api/group";

interface IFormData {
  name: string;
  description: string;
  status: number;
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
          if (!groupId) {
            return;
          }
          // owner / manager 才能改；失败由 unwrap 统一弹提示
          const group = await updateGroup(groupId, values);
          setTitle(group?.name ?? values.name);
          message.success(intl.formatMessage({ id: "flashes.success" }));
        }}
        formKey="group_edit"
        request={async () => {
          const group = await fetchGroup(groupId ?? "");
          setTitle(group.name ?? "");
          document.title = `${group.name}`;
          return {
            name: group.name ?? "",
            description: group.description ?? "",
            status: group.status ?? GROUP_STATUS_PUBLIC,
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
          <ProFormRadio.Group
            name="status"
            label={intl.formatMessage({ id: "labels.group.visibility" })}
            options={[
              {
                value: GROUP_STATUS_PUBLIC,
                label: intl.formatMessage({ id: "labels.group.public" }),
              },
              {
                value: GROUP_STATUS_PRIVATE,
                label: intl.formatMessage({ id: "labels.group.private" }),
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

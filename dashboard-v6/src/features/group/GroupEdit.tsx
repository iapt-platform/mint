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
import { useLoadGuard } from "../../hooks/useLoadGuard";
import ApiErrorResult from "../../components/general/ApiErrorResult";

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
  const load = useLoadGuard();

  // 取不到团队（超时、网络不通…）只显示错误页，不渲染空表单
  if (load.error) {
    return <ApiErrorResult error={load.error} onRetry={load.retry} />;
  }

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
        // formKey 是 request 的缓存键；带上 attempt，「重试」才会真的重新请求
        formKey={`group_edit_${load.attempt}`}
        key={load.attempt}
        request={async () => {
          let group: Awaited<ReturnType<typeof fetchGroup>>;
          try {
            group = await fetchGroup(groupId ?? "");
          } catch (e) {
            load.onError(e);
            throw e;
          }
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

import { useIntl } from "react-intl";
import { ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Button, message, Popover } from "antd";
import { UserAddOutlined } from "@ant-design/icons";
import { get } from "../../request";
import type { IUserListResponse } from "../../api/Auth";
import {
  ASSIGNABLE_GROUP_ROLES,
  addGroupMember,
  type AssignableGroupRole,
} from "../../api/group-member";
import { useState } from "react";

interface IFormData {
  userId: string;
  role: AssignableGroupRole;
}

interface IWidget {
  groupId?: string;
  onCreated?: () => void;
}
const AddMemberWidget = ({ groupId, onCreated }: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);

  const form = (
    <ProForm<IFormData>
      initialValues={{ role: "reader" }}
      onFinish={async (values: IFormData) => {
        if (typeof groupId === "undefined") {
          return;
        }
        // 失败（不是管理者、已是成员等）由 unwrap 统一弹提示
        await addGroupMember(groupId, values.userId, values.role);
        message.success(intl.formatMessage({ id: "flashes.success" }));
        setOpen(false);
        onCreated?.();
      }}
    >
      <ProForm.Group>
        <ProFormSelect
          name="userId"
          label={intl.formatMessage({ id: "forms.fields.user.label" })}
          showSearch
          debounceTime={300}
          request={async ({ keyWords }) => {
            console.log("keyWord", keyWords);
            const json = await get<IUserListResponse>(
              `/api/v2/user?view=key&key=${keyWords}`
            );
            const userList = json.data.rows.map((item) => {
              return {
                value: item.id,
                label: `${item.userName}-${item.nickName}`,
              };
            });
            console.log("json", userList);
            return userList;
          }}
          placeholder={intl.formatMessage({
            id: "forms.message.user.required",
          })}
          rules={[
            {
              required: true,
            },
          ]}
        />
        <ProFormSelect
          name="role"
          label={intl.formatMessage({ id: "labels.group.role" })}
          allowClear={false}
          options={ASSIGNABLE_GROUP_ROLES.map((role) => ({
            value: role,
            label: intl.formatMessage({ id: `labels.group.role.${role}` }),
          }))}
        />
      </ProForm.Group>
    </ProForm>
  );

  return (
    <Popover
      placement="bottomLeft"
      arrow={{ pointAtCenter: true }}
      content={form}
      trigger="click"
      open={open}
      onOpenChange={(open: boolean) => {
        setOpen(open);
      }}
    >
      <Button icon={<UserAddOutlined />} key="add" type="primary">
        {intl.formatMessage({ id: "buttons.group.add.member" })}
      </Button>
    </Popover>
  );
};

export default AddMemberWidget;

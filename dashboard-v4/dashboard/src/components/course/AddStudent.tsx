import { useIntl } from "react-intl";
import { ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Button, message, Popover } from "antd";
import { UserAddOutlined } from "@ant-design/icons";
import { get } from "../../request";

import { IUserListResponse } from "../api/Auth";

interface IFormData {
  userId: string;
}

interface IWidget {
  courseId?: string;
}

const AddStudentWidget = ({ courseId }: IWidget) => {
  const intl = useIntl();

  const form = (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        message.success(intl.formatMessage({ id: "flashes.success" }));
      }}
    >
      <ProForm.Group>
        <ProFormSelect
          name="userId"
          label={intl.formatMessage({ id: "forms.fields.user.label" })}
          showSearch
          debounceTime={300}
          request={async ({ keyWord }) => {
            console.log("keyWord", keyWord);
            const json = await get<IUserListResponse>(`/v2/user?view=key&key=`);
            const userList = json.data.rows.map((item) => {
              return {
                value: item.id,
                label: `${item.userName}-${item.nickName}`,
              };
            });
            console.log("json", userList);
            return userList;
          }}
          placeholder={intl.formatMessage({ id: "forms.fields.user.required" })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "forms.message.user.required",
              }),
            },
          ]}
        />
        <ProFormSelect
          colProps={{ xl: 8, md: 12 }}
          name="userType"
          label={intl.formatMessage({ id: "forms.fields.type.label" })}
          valueEnum={{
            3: intl.formatMessage({ id: "forms.fields.student.label" }),
            2: intl.formatMessage({ id: "forms.fields.assistant.label" }),
          }}
        />
      </ProForm.Group>
    </ProForm>
  );
  return (
    <Popover
      placement="bottom"
      arrowPointAtCenter
      content={form}
      trigger="click"
    >
      <Button icon={<UserAddOutlined />} key="add" type="primary">
        {intl.formatMessage({ id: "buttons.group.add.member" })}
      </Button>
    </Popover>
  );
};

export default AddStudentWidget;

import { useIntl } from "react-intl";
import { ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Button, message, Popover } from "antd";
import { UserAddOutlined } from "@ant-design/icons";

import { get, post } from "../../request";
import { IUserListResponse } from "../api/Auth";
import { useState } from "react";
import {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseRole,
} from "../api/Course";

interface IFormData {
  userId: string;
  role: TCourseRole;
}

interface IWidget {
  courseId?: string;
  onCreated?: Function;
}
const AddMemeberWidget = ({ courseId, onCreated }: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);

  const form = (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        if (typeof courseId !== "undefined") {
          const url = "/v2/course-member";

          const data: ICourseMemberData = {
            user_id: values.userId,
            role: values.role,
            course_id: courseId,
            status: "invited",
          };
          console.info("api request", url, data);
          post<ICourseMemberData, ICourseMemberResponse>(url, data).then(
            (json) => {
              console.log("add member", json);
              if (json.ok) {
                message.success(intl.formatMessage({ id: "flashes.success" }));
                setOpen(false);
                if (typeof onCreated !== "undefined") {
                  onCreated();
                }
              }
            }
          );
        }
      }}
    >
      <ProForm.Group>
        <ProFormSelect
          width="sm"
          name="userId"
          label={intl.formatMessage({ id: "forms.fields.user.label" })}
          showSearch
          debounceTime={300}
          request={async ({ keyWords }) => {
            console.log("keyWord", keyWords);
            const json = await get<IUserListResponse>(
              `/v2/user?view=key&key=${keyWords}`
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
              message: intl.formatMessage({
                id: "forms.message.user.required",
              }),
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormSelect
          width="sm"
          name="role"
          label={intl.formatMessage({ id: "forms.fields.type.label" })}
          valueEnum={{
            student: intl.formatMessage({ id: "forms.fields.student.label" }),
            assistant: intl.formatMessage({
              id: "forms.fields.assistant.label",
            }),
            manager: intl.formatMessage({
              id: "auth.role.manager",
            }),
          }}
        />
      </ProForm.Group>
    </ProForm>
  );
  const handleClickChange = (open: boolean) => {
    setOpen(open);
  };
  return (
    <Popover
      placement="bottomLeft"
      arrowPointAtCenter
      content={form}
      trigger="click"
      open={open}
      onOpenChange={handleClickChange}
    >
      <Button icon={<UserAddOutlined />} key="add" type="primary">
        {intl.formatMessage({ id: "buttons.group.add.member" })}
      </Button>
    </Popover>
  );
};

export default AddMemeberWidget;

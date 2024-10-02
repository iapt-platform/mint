import { PlusOutlined } from "@ant-design/icons";
import {
  ProFormInstance,
  ProFormSelect,
  StepsForm,
} from "@ant-design/pro-components";
import { Alert, Button, Modal, Result, message } from "antd";
import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import { get, post } from "../../request";
import { ICourseMemberData, ICourseMemberResponse } from "../api/Course";
import { IUserListResponse } from "../api/Auth";

interface IFormData {
  userId: string;
  role: string;
}

interface IWidget {
  courseId?: string;
  onCreated?: Function;
}

const CourseInviteWidget = ({ courseId, onCreated }: IWidget) => {
  const intl = useIntl();
  const [visible, setVisible] = useState(false);
  const [curr, setCurr] = useState<ICourseMemberData>();
  const [userId, setUserId] = useState<string>();

  const formRef = useRef<ProFormInstance>();

  return (
    <>
      <Button type="primary" onClick={() => setVisible(true)}>
        <PlusOutlined />
        邀请
      </Button>
      <Modal
        title="邀请"
        width={600}
        onCancel={() => setVisible(false)}
        open={visible}
        footer={false}
        destroyOnClose
      >
        <StepsForm<IFormData>
          formRef={formRef}
          onFinish={async (values) => {
            console.log(values);
            setVisible(false);
            message.success("提交成功");
          }}
          formProps={{
            validateMessages: {
              required: "此项为必填项",
            },
          }}
        >
          <StepsForm.StepForm
            name="base"
            title="选择用户"
            onFinish={async (values) => {
              setUserId(values.userId);
              const url = `/v2/course-member/${courseId}?user_uid=${values.userId}`;
              console.info("api request", url, values);
              const json = await get<ICourseMemberResponse>(url);
              if (json.ok) {
                setCurr(json.data);
              } else {
                setCurr(undefined);
              }
              return true;
            }}
          >
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
          </StepsForm.StepForm>
          <StepsForm.StepForm
            name="checkbox"
            title="选择身份"
            onFinish={async (values) => {
              if (typeof courseId !== "undefined" && userId) {
                const url = "/v2/course-member";
                const data: ICourseMemberData = {
                  user_id: userId,
                  role: values.role,
                  course_id: courseId,
                  status: "invited",
                };
                console.info("api request", url, data);
                const json = await post<
                  ICourseMemberData,
                  ICourseMemberResponse
                >(url, data);

                console.info("add member api response", json);
                if (json.ok) {
                  if (typeof onCreated !== "undefined") {
                    onCreated();
                  }
                } else {
                  console.error(json.message);
                  return false;
                }
              } else {
                return false;
              }
              return true;
            }}
          >
            {curr ? (
              <Alert
                message={`用户 ${curr?.user?.nickName} 身份 ${curr?.role} 状态 ${curr?.status} `}
              />
            ) : (
              <></>
            )}
            <ProFormSelect
              width="sm"
              name="role"
              label={intl.formatMessage({ id: "forms.fields.type.label" })}
              valueEnum={{
                student: intl.formatMessage({
                  id: "forms.fields.student.label",
                }),
                assistant: intl.formatMessage({
                  id: "forms.fields.assistant.label",
                }),
                manager: intl.formatMessage({
                  id: "auth.role.manager",
                }),
              }}
            />
          </StepsForm.StepForm>
          <StepsForm.StepForm name="time" title="完成">
            <Result status="success" title="已经成功邀请" />
          </StepsForm.StepForm>
        </StepsForm>
      </Modal>
    </>
  );
};

export default CourseInviteWidget;

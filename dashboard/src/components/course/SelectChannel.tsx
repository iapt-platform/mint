import { ModalForm, ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Alert, Button, message } from "antd";
import { GlobalOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

import { get, put } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";
import { LockIcon } from "../../assets/icon";
import { ICourseMemberData, ICourseMemberResponse } from "../api/Course";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
interface IWidget {
  courseId?: string | null;
  exerciseId?: string;
  channel?: string;
  onSelected?: Function;
  open?: boolean;
  onOpenChange?: Function;
}
const SelectChannelWidget = ({
  courseId,
  exerciseId,
  channel,
  onSelected,
  open,
  onOpenChange,
}: IWidget) => {
  const user = useAppSelector(_currentUser);
  const intl = useIntl();
  const { id } = useParams(); //url 参数

  return (
    <Alert
      message={`请选择作业的存放位置`}
      type="warning"
      action={
        <ModalForm<{
          channel: string;
        }>
          title="请选择作业的存放位置"
          trigger={
            <Button type="primary">
              {intl.formatMessage({
                id: "buttons.select",
              })}
            </Button>
          }
          autoFocusFirstInput
          modalProps={{
            destroyOnClose: true,
            onCancel: () => console.log("run"),
          }}
          submitTimeout={20000}
          onFinish={async (values) => {
            if (user && courseId) {
              const url = `/v2/course-member_set-channel`;
              const data = {
                user_id: user.id,
                course_id: courseId,
                channel_id: values.channel,
              };
              console.info("course select channel api request", url, data);
              const json = await put<ICourseMemberData, ICourseMemberResponse>(
                url,
                data
              );
              console.info("course select channel api response", json);
              if (json.ok) {
                message.success("提交成功");
                if (typeof onSelected !== "undefined") {
                  onSelected();
                }
              } else {
                message.error(json.message);
                return false;
              }
            } else {
              console.log("select channel error:", user, courseId);
              return false;
            }

            return true;
          }}
          onOpenChange={(visible: boolean) => {
            if (typeof onOpenChange !== "undefined") {
              onOpenChange(visible);
            }
          }}
        >
          <div>
            您还没有选择版本。您将用一个版本保存自己的作业。这个版本里面的内容将会被老师，助理老师看到。
          </div>
          <ProForm.Group>
            <ProFormSelect
              rules={[
                {
                  required: true,
                },
              ]}
              request={async () => {
                const channelData = await get<IApiResponseChannelList>(
                  `/v2/channel?view=studio&name=${user?.realName}`
                );
                const channel = channelData.data.rows.map((item) => {
                  const icon =
                    item.status === 30 ? <GlobalOutlined /> : <LockIcon />;
                  return {
                    value: item.uid,
                    label: (
                      <>
                        {icon} {item.name}
                      </>
                    ),
                  };
                });
                return channel;
              }}
              width="md"
              name="channel"
              label="版本风格"
            />
          </ProForm.Group>
        </ModalForm>
      }
    />
  );
};

export default SelectChannelWidget;

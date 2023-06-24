import { ModalForm, ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Button, message } from "antd";
import { GlobalOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

import { get, put } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";
import { LockIcon } from "../../assets/icon";
import { ICourseMemberData, ICourseMemberResponse } from "../api/Course";
import { useNavigate, useParams } from "react-router-dom";
interface IWidget {
  courseId?: string;
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
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  //TODO 从哪里拿到courseId?
  return (
    <ModalForm<{
      channel: string;
    }>
      title="选择作业的存放位置"
      trigger={<Button>做练习</Button>}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        console.log(values.channel);
        console.log("id", id);
        const mCourseId = id?.split("_")[0];
        if (typeof user !== "undefined" && typeof mCourseId !== "undefined") {
          const json = await put<ICourseMemberData, ICourseMemberResponse>(
            `/v2/course-member_set-channel`,
            {
              user_id: user.id,
              course_id: mCourseId,
              channel_id: values.channel,
            }
          );
          if (json.ok) {
            if (json.data.channel_id === courseId) {
              message.success("提交成功");
              navigate(
                `/article/exercise/${id}_${exerciseId}_${user.realName}/wbw`
              );
            } else {
              message.error(json.data.channel_id);
            }
          } else {
            message.error(json.message);
          }
        } else {
          console.log("select channel error:", user, courseId);
        }

        return true;
      }}
    >
      <div>
        您还没有选择版本。您将用一个版本保存自己的作业。这个版本将会被老师，助理老师看到。
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
  );
};

export default SelectChannelWidget;

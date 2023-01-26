import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormDateRangePicker,
  ProFormSelect,
  ProFormUploadButton,
  RequestOptionsType,
} from "@ant-design/pro-components";
import { UsergroupAddOutlined } from "@ant-design/icons";
import { Card, message, Form, Button, Drawer } from "antd";

import { API_HOST, get, put } from "../../../request";
import {
  ICourseDataRequest,
  ICourseDataResponse,
  ICourseResponse,
} from "../../../components/api/Course";
import PublicitySelect from "../../../components/studio/PublicitySelect";
import GoBack from "../../../components/studio/GoBack";

import LessonSelect from "../../../components/library/course/LessonSelect";
import { IUserListResponse } from "../../../components/api/Auth";
import MDEditor from "@uiw/react-md-editor";
import { DefaultOptionType } from "antd/lib/select";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../../components/api/Attachments";
import StudentsSelect from "../../../components/library/course/StudentsSelect";
import CourseMember from "../../../components/course/CourseMember";
import { IAnthologyListResponse } from "../../../components/api/Article";
import { IApiResponseChannelList } from "../../../components/api/Channel";

interface IFormData {
  title: string;
  subtitle: string;
  content?: string;
  cover?: UploadFile<IAttachmentResponse>[];
  teacherId?: string;
  anthologyId?: string;
  channelId?: string;
  dateRange?: Date[];
  status: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  const [contentValue, setContentValue] = useState<string>();
  const [teacherOption, setTeacherOption] = useState<DefaultOptionType[]>([]);
  const [currTeacher, setCurrTeacher] = useState<RequestOptionsType>();
  const [textbookOption, setTextbookOption] = useState<DefaultOptionType[]>([]);
  const [currTextbook, setCurrTextbook] = useState<RequestOptionsType>();
  const [channelOption, setChannelOption] = useState<DefaultOptionType[]>([]);
  const [currChannel, setCurrChannel] = useState<RequestOptionsType>();
  const [openMember, setOpenMember] = useState(false);
  const [courseData, setCourseData] = useState<ICourseDataResponse>();

  return (
    <>
      <Card
        title={
          <GoBack to={`/studio/${studioname}/course/list`} title={title} />
        }
        extra={
          <Button
            icon={<UsergroupAddOutlined />}
            onClick={() => {
              setOpenMember(true);
            }}
          >
            成员
          </Button>
        }
      >
        <ProForm<IFormData>
          formKey="course_edit"
          onFinish={async (values: IFormData) => {
            console.log("all data", values);
            let startAt: string, endAt: string;
            let _cover: string = "";
            if (typeof values.dateRange === "undefined") {
              startAt = "";
              endAt = "";
            } else if (
              typeof values.dateRange[0] === "string" &&
              typeof values.dateRange[1] === "string"
            ) {
              startAt = values.dateRange[0];
              endAt = values.dateRange[1];
            } else {
              startAt = courseData ? courseData.start_at : "";
              endAt = courseData ? courseData.end_at : "";
            }

            if (
              typeof values.cover === "undefined" ||
              values.cover.length === 0
            ) {
              _cover = "";
            } else if (typeof values.cover[0].response === "undefined") {
              _cover = values.cover[0].uid;
            } else {
              _cover = values.cover[0].response.data.url;
            }

            const res = await put<ICourseDataRequest, ICourseResponse>(
              `/v2/course/${courseId}`,
              {
                title: values.title, //标题
                subtitle: values.subtitle, //副标题
                content: contentValue, //简介
                cover: _cover, //封面图片文件名
                teacher_id: values.teacherId, //UserID
                publicity: values.status, //类型-公开/内部
                anthology_id: values.anthologyId, //文集ID
                channel_id: values.channelId,
                start_at: startAt, //课程开始时间
                end_at: endAt, //课程结束时间
              }
            );
            console.log(res);
            if (res.ok) {
              message.success(intl.formatMessage({ id: "flashes.success" }));
            } else {
              message.error(res.message);
            }
          }}
          request={async () => {
            const res = await get<ICourseResponse>(`/v2/course/${courseId}`);
            setCourseData(res.data);
            setTitle(res.data.title);
            console.log(res.data);
            setContentValue(res.data.content);
            if (res.data.teacher) {
              console.log("teacher", res.data.teacher);
              const teacher = {
                value: res.data.teacher.id,
                label: res.data.teacher.nickName,
              };
              setCurrTeacher(teacher);
              setTeacherOption([teacher]);
              const textbook = {
                value: res.data.anthology_id,
                label:
                  res.data.anthology_owner?.nickName +
                  "/" +
                  res.data.anthology_title,
              };
              setCurrTextbook(textbook);
              setTextbookOption([textbook]);
              const channel = {
                value: res.data.channel_id,
                label:
                  res.data.channel_owner?.nickName +
                  "/" +
                  res.data.channel_name,
              };
              setCurrChannel(channel);
              setChannelOption([channel]);
            }
            return {
              title: res.data.title,
              subtitle: res.data.subtitle,
              content: res.data.content,
              cover: res.data.cover
                ? [
                    {
                      uid: res.data.cover,
                      name: "cover",
                      thumbUrl: API_HOST + "/" + res.data.cover,
                    },
                  ]
                : [],
              teacherId: res.data.teacher?.id,
              anthologyId: res.data.anthology_id,
              channelId: res.data.channel_id,
              dateRange:
                res.data.start_at && res.data.end_at
                  ? [new Date(res.data.start_at), new Date(res.data.end_at)]
                  : undefined,
              status: res.data.publicity,
            };
          }}
        >
          <ProForm.Group>
            <ProFormUploadButton
              name="cover"
              label="封面"
              max={1}
              fieldProps={{
                name: "file",
                listType: "picture-card",
                className: "avatar-uploader",
              }}
              action={`${API_HOST}/api/v2/attachments`}
              extra="封面必须为正方形。最大512*512"
            />
          </ProForm.Group>
          <ProForm.Group>
            <ProFormText
              width="md"
              name="title"
              required
              label={intl.formatMessage({
                id: "forms.fields.title.label",
              })}
              rules={[
                {
                  required: true,
                },
              ]}
            />
            <ProFormText
              width="md"
              name="subtitle"
              label={intl.formatMessage({
                id: "forms.fields.subtitle.label",
              })}
            />
          </ProForm.Group>

          <ProForm.Group>
            <ProFormSelect
              options={teacherOption}
              width="md"
              name="teacherId"
              label={intl.formatMessage({ id: "forms.fields.teacher.label" })}
              showSearch
              debounceTime={300}
              request={async ({ keyWords }) => {
                console.log("keyWord", keyWords);
                if (typeof keyWords === "undefined") {
                  return currTeacher ? [currTeacher] : [];
                }
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
                id: "forms.fields.teacher.label",
              })}
            />
            <ProFormDateRangePicker
              width="md"
              name="dateRange"
              label="课程区间"
            />
          </ProForm.Group>

          <ProForm.Group>
            <ProFormSelect
              options={textbookOption}
              width="md"
              name="anthologyId"
              label={intl.formatMessage({ id: "forms.fields.textbook.label" })}
              showSearch
              debounceTime={300}
              request={async ({ keyWords }) => {
                console.log("keyWord", keyWords);
                if (typeof keyWords === "undefined") {
                  return currTextbook ? [currTextbook] : [];
                }
                const json = await get<IAnthologyListResponse>(
                  `/v2/anthology?view=public`
                );
                const textbookList = json.data.rows.map((item) => {
                  return {
                    value: item.uid,
                    label: `${item.studio.nickName}/${item.title}`,
                  };
                });
                console.log("json", textbookList);
                return textbookList;
              }}
            />
            <ProFormSelect
              options={channelOption}
              width="md"
              name="channelId"
              label={"标准答案"}
              showSearch
              debounceTime={300}
              request={async ({ keyWords }) => {
                console.log("keyWord", keyWords);
                if (typeof keyWords === "undefined") {
                  return currChannel ? [currChannel] : [];
                }
                const json = await get<IApiResponseChannelList>(
                  `/v2/channel?view=studio&name=${studioname}`
                );
                const textbookList = json.data.rows.map((item) => {
                  return {
                    value: item.uid,
                    label: `${item.studio.nickName}/${item.name}`,
                  };
                });
                console.log("json", textbookList);
                return textbookList;
              }}
            />
          </ProForm.Group>
          <ProForm.Group>
            <PublicitySelect />
          </ProForm.Group>
          <ProForm.Group>
            <Form.Item
              name="content"
              label={intl.formatMessage({ id: "forms.fields.summary.label" })}
            >
              <MDEditor
                value={contentValue}
                onChange={(value: string | undefined) => {
                  if (value) {
                    setContentValue(value);
                  }
                }}
              />
            </Form.Item>
          </ProForm.Group>
        </ProForm>
      </Card>
      <Drawer
        title="课程成员"
        placement="right"
        onClose={() => {
          setOpenMember(false);
        }}
        open={openMember}
      >
        <CourseMember courseId={courseId} />
      </Drawer>
    </>
  );
};

export default Widget;

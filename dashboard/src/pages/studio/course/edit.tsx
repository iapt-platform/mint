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

interface IFormData {
  title: string;
  subtitle: string;
  content: string;
  cover: UploadFile<IAttachmentResponse>[];
  teacherId: string;
  anthologyId: string;
  dateRange?: Date[];
}

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  const [contentValue, setContentValue] = useState("");
  const [teacherOption, setTeacherOption] = useState<DefaultOptionType[]>([]);
  const [currTeacher, setCurrTeacher] = useState<RequestOptionsType>();
  const [openMember, setOpenMember] = useState(false);
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
            console.log(
              "start",
              values.dateRange ? values.dateRange[0].toString() : ""
            );
            console.log(values.cover);
            const res = await put<ICourseDataRequest, ICourseResponse>(
              `/v2/course/${courseId}`,
              {
                title: values.title, //标题
                subtitle: values.subtitle, //副标题
                content: contentValue, //简介
                cover: values.cover[0].response?.data.url, //封面图片文件名
                teacher_id: values.teacherId, //UserID
                type: 1, //类型-公开/内部
                anthology_id: values.anthologyId, //文集ID
                start_at: values.dateRange
                  ? values.dateRange[0].toString()
                  : undefined, //课程开始时间
                end_at: values.dateRange
                  ? values.dateRange[1].toString()
                  : undefined, //课程结束时间
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
            setTitle(res.data.title);
            console.log(res.data);
            setContentValue(res.data.content);
            if (res.data.teacher) {
              console.log("teacher", res.data.teacher);
              setCurrTeacher({
                value: res.data.teacher.id,
                label: res.data.teacher.nickName,
              });
              setTeacherOption([
                {
                  value: res.data.teacher.id,
                  label: res.data.teacher.nickName,
                },
              ]);
            }
            return {
              title: res.data.title,
              subtitle: res.data.subtitle,
              content: res.data.content,
              cover: res.data.cover
                ? [
                    {
                      uid: "1",
                      name: "cover",
                      thumbUrl: API_HOST + "/" + res.data.cover,
                    },
                  ]
                : [],
              teacherId: res.data.teacher?.id,
              anthologyId: res.data.anthology_id,
              dateRange: res.data.start_at
                ? [new Date(res.data.start_at), new Date(res.data.end_at)]
                : undefined,
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

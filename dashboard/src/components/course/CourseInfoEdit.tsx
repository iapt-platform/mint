import { useState } from "react";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormDateRangePicker,
  ProFormSelect,
  ProFormUploadButton,
  RequestOptionsType,
  ProFormDependency,
  ProFormDigit,
} from "@ant-design/pro-components";

import { message, Form } from "antd";
import { get as getToken } from "../../reducers/current-user";

import { API_HOST, get, put } from "../../request";
import {
  ICourseDataRequest,
  ICourseDataResponse,
  ICourseResponse,
} from "../../components/api/Course";
import PublicitySelect from "../../components/studio/PublicitySelect";

import { IUserListResponse } from "../../components/api/Auth";
import MDEditor from "@uiw/react-md-editor";
import { DefaultOptionType } from "antd/lib/select";
import { UploadChangeParam, UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../components/api/Attachments";

import { IAnthologyListResponse } from "../../components/api/Article";
import {
  IApiResponseChannelData,
  IApiResponseChannelList,
} from "../../components/api/Channel";

interface IFormData {
  title: string;
  subtitle: string;
  summary?: string;
  content?: string | null;
  cover?: UploadFile<IAttachmentResponse>[];
  teacherId?: string;
  anthologyId?: string;
  channelId?: string;
  signUpMessage?: string | null;
  dateRange?: string[];
  signUp?: string[];
  status: number;
  join: string;
  exp: string;
  number: number;
}

interface IWidget {
  studioName?: string;
  courseId?: string;
  onTitleChange?: Function;
}
const CourseInfoEditWidget = ({
  studioName,
  courseId,
  onTitleChange,
}: IWidget) => {
  const intl = useIntl();
  const [teacherOption, setTeacherOption] = useState<DefaultOptionType[]>([]);
  const [currTeacher, setCurrTeacher] = useState<RequestOptionsType>();
  const [textbookOption, setTextbookOption] = useState<DefaultOptionType[]>([]);
  const [currTextbook, setCurrTextbook] = useState<RequestOptionsType>();
  const [channelOption, setChannelOption] = useState<DefaultOptionType[]>([]);
  const [currChannel, setCurrChannel] = useState<RequestOptionsType>();
  const [courseData, setCourseData] = useState<ICourseDataResponse>();

  return (
    <div>
      <ProForm<IFormData>
        formKey="course_edit"
        onFinish={async (values: IFormData) => {
          console.log("course put all data", values);
          let _cover: string = "";
          const startAt = values.dateRange ? values.dateRange[0] : "";
          const endAt = values.dateRange ? values.dateRange[1] : "";
          const signUpStartAt = values.signUp ? values.signUp[0] : null;
          const signUpEndAt = values.signUp ? values.signUp[1] : null;
          if (
            typeof values.cover === "undefined" ||
            values.cover.length === 0
          ) {
            _cover = "";
          } else if (typeof values.cover[0].response === "undefined") {
            _cover = values.cover[0].uid;
          } else {
            console.debug("upload ", values.cover[0].response);
            _cover = values.cover[0].response.data.name;
          }
          const url = `/v2/course/${courseId}`;
          const postData: ICourseDataRequest = {
            title: values.title, //标题
            subtitle: values.subtitle, //副标题
            summary: values.summary,
            content: values.content, //简介
            sign_up_message: values.signUpMessage,
            cover: _cover, //封面图片文件名
            teacher_id: values.teacherId, //UserID
            publicity: values.status, //类型-公开/内部
            anthology_id: values.anthologyId, //文集ID
            channel_id: values.channelId,
            start_at: startAt, //课程开始时间
            end_at: endAt, //课程结束时间
            sign_up_start_at: signUpStartAt,
            sign_up_end_at: signUpEndAt,
            join: values.join,
            request_exp: values.exp,
            number: values.number,
          };
          console.debug("course info edit put", url, postData);
          const res = await put<ICourseDataRequest, ICourseResponse>(
            url,
            postData
          );
          console.debug("course info edit put", res);
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
          } else {
            message.error(res.message);
          }
        }}
        request={async () => {
          const res = await get<ICourseResponse>(`/v2/course/${courseId}`);
          console.log("course data", res.data);
          setCourseData(res.data);
          if (typeof onTitleChange !== "undefined") {
            onTitleChange(res.data.title);
          }
          console.log(res.data);
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
                res.data.channel_owner?.nickName + "/" + res.data.channel_name,
            };
            setCurrChannel(channel);
            setChannelOption([channel]);
          }
          return {
            title: res.data.title,
            subtitle: res.data.subtitle,
            summary: res.data.summary,
            content: res.data.content ?? "",
            signUpMessage: res.data.sign_up_message,
            cover: res.data.cover
              ? [
                  {
                    uid: res.data.cover,
                    name: "cover",
                    thumbUrl:
                      res.data.cover_url && res.data.cover_url.length > 1
                        ? res.data.cover_url[1]
                        : undefined,
                  },
                ]
              : [],
            teacherId: res.data.teacher?.id,
            anthologyId: res.data.anthology_id,
            channelId: res.data.channel_id,
            dateRange: [res.data.start_at, res.data.end_at],
            signUp: [res.data.sign_up_start_at, res.data.sign_up_end_at],
            status: res.data.publicity,
            join: res.data.join,
            exp: res.data.request_exp,
            number: res.data.number,
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
              headers: {
                Authorization: `Bearer ${getToken()}`,
              },
              onRemove: (file: UploadFile<any>): boolean => {
                console.log("remove", file);
                return true;
              },
            }}
            action={`${API_HOST}/api/v2/attachment`}
            extra="封面必须为正方形。最大512*512"
            onChange={(info: UploadChangeParam<UploadFile<any>>) => {}}
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
          <ProFormDigit label="招生数量" name="number" min={0} />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormDateRangePicker width="md" name="signUp" label="报名时间" />
          <ProFormDateRangePicker
            width="md"
            name="dateRange"
            label="课程时间"
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
              if (typeof keyWords === "undefined" || keyWords === " ") {
                return currChannel ? [currChannel] : [];
              }
              let urlMy = `/v2/channel?view=studio-all&name=${studioName}`;
              if (typeof keyWords !== "undefined" && keyWords !== "") {
                urlMy += "&search=" + keyWords;
              }
              console.info("api request", urlMy);
              const json = await get<IApiResponseChannelList>(urlMy);
              console.info("api response", json);

              let urlPublic = `/v2/channel?view=public`;
              if (typeof keyWords !== "undefined" && keyWords !== "") {
                urlPublic += "&search=" + keyWords;
              }
              console.info("api request", urlPublic);
              const jsonPublic = await get<IApiResponseChannelList>(urlPublic);
              console.info("api response", jsonPublic);

              //查重
              let channels1: IApiResponseChannelData[] = [];
              const channels = [...json.data.rows, ...jsonPublic.data.rows];
              channels.forEach((value) => {
                const has = channels1.findIndex(
                  (value1) => value1.uid === value.uid
                );
                if (has === -1) {
                  channels1.push(value);
                }
              });

              const channelList = channels1.map((item) => {
                return {
                  value: item.uid,
                  label: `${item.studio.nickName}/${item.name}`,
                };
              });
              console.debug("channelList", channelList);
              return channelList;
            }}
          />
        </ProForm.Group>
        <ProForm.Group>
          <PublicitySelect width="md" disable={["blocked"]} />
          <ProFormDependency name={["status"]}>
            {({ status }) => {
              const option = [
                {
                  value: "invite",
                  label: intl.formatMessage({
                    id: "course.join.mode.invite.label",
                  }),
                  disabled: false,
                },
                {
                  value: "manual",
                  label: intl.formatMessage({
                    id: "course.join.mode.manual.label",
                  }),
                  disabled: false,
                },
                {
                  value: "open",
                  label: intl.formatMessage({
                    id: "course.join.mode.open.label",
                  }),
                  disabled: false,
                },
              ];
              if (status === 10) {
                option[1].disabled = true;
                option[2].disabled = true;
              } else {
                option[0].disabled = true;
              }
              return (
                <ProFormSelect
                  options={option}
                  width="md"
                  name="join"
                  allowClear={false}
                  label="录取方式"
                />
              );
            }}
          </ProFormDependency>
        </ProForm.Group>
        <ProForm.Group>
          <ProFormDependency name={["join"]}>
            {({ join }) => {
              const option = [
                {
                  value: "none",
                  label: intl.formatMessage({
                    id: "course.exp.request.none.label",
                  }),
                  disabled: false,
                },
                {
                  value: "begin-end",
                  label: intl.formatMessage({
                    id: "course.exp.request.begin-end.label",
                  }),
                  disabled: false,
                },
                {
                  value: "daily",
                  label: intl.formatMessage({
                    id: "course.exp.request.daily.label",
                  }),
                  disabled: false,
                },
              ];
              if (join === "open") {
                option[1].disabled = true;
                option[2].disabled = true;
              }
              return (
                <ProFormSelect
                  hidden
                  tooltip="要求查看经验值，需要学生同意才会生效。"
                  options={option}
                  width="md"
                  name="exp"
                  label="查看学生经验值"
                  allowClear={false}
                />
              );
            }}
          </ProFormDependency>
        </ProForm.Group>
        <ProForm.Group>
          <Form.Item
            name="signUpMessage"
            label={intl.formatMessage({
              id: "forms.fields.sign-up-message.label",
            })}
          >
            <MDEditor />
          </Form.Item>
        </ProForm.Group>
        <ProForm.Group>
          <Form.Item
            name="content"
            label={intl.formatMessage({ id: "forms.fields.content.label" })}
          >
            <MDEditor />
          </Form.Item>
        </ProForm.Group>
      </ProForm>
    </div>
  );
};

export default CourseInfoEditWidget;

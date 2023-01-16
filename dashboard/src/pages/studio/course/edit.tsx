import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl, FormattedMessage } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
  ProFormDateRangePicker,
} from "@ant-design/pro-components";
import { Card, message, Col, Row, Divider, Tabs } from "antd";
import { get, put } from "../../../request";
import { marked } from "marked";
import {
  ICourseDataRequest,
  ICourseResponse,
} from "../../../components/api/Course";
import PublicitySelect from "../../../components/studio/PublicitySelect";
import GoBack from "../../../components/studio/GoBack";
import UploadTexture from "../../../components/library/course/UploadTexture";
import TeacherSelect from "../../../components/library/course/TeacherSelect";
import StudentsSelect from "../../../components/library/course/StudentsSelect";
import LessonSelect from "../../../components/library/course/LessonSelect";
import LessonTreeShow from "../../../components/library/course/LessonTreeShow";

interface IFormData {
  uid: string;
  title: string;

  t_type: string;
  status: number;
  lang: string;
}
const onChange = (key: string) => {
  console.log(key);
};
let groupid = "1";

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");

  return (
    <Tabs
      onChange={onChange}
      type="card"
      items={[
        {
          label: `基本信息`,
          key: "1",
          children: (
            <Card
              title={
                <GoBack
                  to={`/studio/${studioname}/course/list`}
                  title={title}
                />
              }
            >
              <ProForm<IFormData>
                onFinish={async (values: IFormData) => {
                  // TODO
                  let request = {
                    uid: courseId?.toString,
                    title: "课程" + courseId,
                    subtitle: "课程副标题" + courseId,
                    teacher: 1,
                    course_count: 2,
                    type: 30,
                    created_at: "",
                    updated_at: "",
                    article_id: 1, //"1e642dac-dcb2-468a-8cc7-0228e5ca6ac4",
                    course_start_at: "", //课程开始时间
                    course_end_at: "", //课程结束时间
                    intro_markdown: "", //简介
                    cover_img_name: "", //封面图片文件名
                  };
                }}
                /*		    const request = {
    uid: courseid ? courseid : "",
    title: values.title,
    subtitle: values.subtitle,
    teacher: values.teacher,//UserID
    course_count: values.course_count,//课程数
    type: values.type,//类型-公开/内部
    created_at: values.created_at,//创建时间
    updated_at: values.updated_at,//修改时间
    article_id: values.article_id,//文集ID
    course_start_at: values.course_start_at,//课程开始时间
    course_end_at: values.course_end_at,//课程结束时间
    intro_markdown: values.intro_markdown,//简介
    cover_img_name: values.cover_img_name,//封面图片文件名
  };
  console.log(request);
  const res = await put<ICourseDataRequest, ICourseResponse>(
    `/v2/course/${courseid}`,
    request
  );
  console.log(res);
  if (res.ok) {
    message.success(intl.formatMessage({ id: "flashes.success" }));
  } else {
    message.error(res.message);
  }
}}
request={async () => {
  const res = await get<ICourseResponse>(`/v2/course/${courseid}`);
  setTitle(res.data.title);
  return {
    uid: res.data.uid,
    title: res.data.title,
    subtitle: res.data.subtitle,
    summary: res.data.summary,
    content: res.data.content,
    content_type: res.data.content_type,
    lang: res.data.lang,
    status: res.data.status,
  };
}}*/
              >
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
                        message: intl.formatMessage({
                          id: "forms.message.title.required",
                        }),
                      },
                    ]}
                  />
                </ProForm.Group>
                <ProForm.Group>
                  <ProFormText
                    width="md"
                    name="subtitle"
                    label={intl.formatMessage({
                      id: "forms.fields.subtitle.label",
                    })}
                  />
                </ProForm.Group>
                <ProForm.Group>
                  <p style={{ fontWeight: "bold", fontSize: 15 }}>
                    <FormattedMessage id="forms.fields.upload.texture" />{" "}
                  </p>
                  <UploadTexture />
                </ProForm.Group>
                <ProForm.Group>
                  <ProFormDateRangePicker
                    width="md"
                    name={["contract", "createTime"]}
                    label="课程区间"
                  />
                </ProForm.Group>
                <ProForm.Group>
                  <PublicitySelect />
                </ProForm.Group>
                <Divider />

                <Row>
                  <Col flex="400px">
                    <TeacherSelect groupId={groupid} />
                  </Col>
                </Row>
                <Divider />
                <Row>
                  <Col flex="400px">
                    <LessonSelect groupId={groupid} />
                  </Col>
                </Row>
                <Divider />
                <ProForm.Group>
                  <ProFormTextArea
                    name="summary"
                    width="md"
                    label={intl.formatMessage({
                      id: "forms.fields.summary.label",
                    })}
                  />

                  <p style={{ fontWeight: "bold", fontSize: 15 }}>
                    <FormattedMessage id="forms.fields.markdown.label" />{" "}
                  </p>
                  <Row>
                    <div
                      dangerouslySetInnerHTML={{
                        __html: marked.parse(
                          "# 这是标题\n" +
                            "[ **M** ] arkdown + E [ **ditor** ] = **Mditor**  \n" +
                            "**这是加粗的文字**\n\n" +
                            "*这是倾斜的文字*`\n\n" +
                            "***这是斜体加粗的文字***\n\n" +
                            "~~这是加删除线的文字~~ \n\n"
                        ),
                      }}
                    ></div>
                  </Row>
                </ProForm.Group>
              </ProForm>
            </Card>
          ),
        },
        {
          label: `学生与助教选择 `,
          key: "2",
          children: (
            <Card
              title={
                <GoBack
                  to={`/studio/${studioname}/course/list`}
                  title={title}
                />
              }
            >
              <ProForm<IFormData> onFinish={async (values: IFormData) => {}}>
                <ProForm.Group>
                  <LessonTreeShow />
                </ProForm.Group>
                <ProForm.Group></ProForm.Group>

                <Row>
                  <Col flex="400px">
                    <StudentsSelect groupId={groupid} />
                  </Col>
                </Row>
              </ProForm>
            </Card>
          ),
        },
      ]}
    />
  );
};

export default Widget;

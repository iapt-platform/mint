import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Button, Card, Form, message, Space, Tabs } from "antd";

import { get, put } from "../../../request";
import {
  IArticleDataRequest,
  IArticleResponse,
} from "../../../components/api/Article";
import LangSelect from "../../../components/general/LangSelect";
import PublicitySelect from "../../../components/studio/PublicitySelect";
import GoBack from "../../../components/studio/GoBack";
import MDEditor from "@uiw/react-md-editor";
import ArticleTplMaker from "../../../components/article/ArticleTplMaker";

interface IFormData {
  uid: string;
  title: string;
  subtitle: string;
  summary: string;
  content?: string;
  content_type?: string;
  status: number;
  lang: string;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname, articleid } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");

  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/article/list`} title={title} />}
      extra={
        <ArticleTplMaker
          title={title}
          type="article"
          id={articleid}
          trigger={<Button>模版</Button>}
        />
      }
    >
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          // TODO

          const request = {
            uid: articleid ? articleid : "",
            title: values.title,
            subtitle: values.subtitle,
            summary: values.summary,
            content: values.content,
            content_type: "markdown",
            status: values.status,
            lang: values.lang,
          };
          console.log(request);
          const res = await put<IArticleDataRequest, IArticleResponse>(
            `/v2/article/${articleid}`,
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
          const res = await get<IArticleResponse>(`/v2/article/${articleid}`);
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
        }}
      >
        <Tabs
          items={[
            {
              key: "info",
              label: intl.formatMessage({ id: "course.basic.info.label" }),
              children: (
                <>
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
                    <ProFormText
                      width="md"
                      name="subtitle"
                      label={intl.formatMessage({
                        id: "forms.fields.subtitle.label",
                      })}
                    />
                  </ProForm.Group>
                  <ProForm.Group>
                    <LangSelect width="md" />
                    <PublicitySelect width="md" />
                  </ProForm.Group>
                  <ProForm.Group>
                    <ProFormTextArea
                      name="summary"
                      width="lg"
                      label={intl.formatMessage({
                        id: "forms.fields.summary.label",
                      })}
                    />
                  </ProForm.Group>
                </>
              ),
            },
            {
              key: "content",
              label: intl.formatMessage({ id: "forms.fields.content.label" }),
              forceRender: true,
              children: (
                <ProForm.Group>
                  <Form.Item
                    name="content"
                    label={
                      <Space>
                        {intl.formatMessage({
                          id: "forms.fields.content.label",
                        })}
                        <Button>预览</Button>
                      </Space>
                    }
                  >
                    <MDEditor />
                  </Form.Item>
                </ProForm.Group>
              ),
            },
          ]}
        />
      </ProForm>
    </Card>
  );
};

export default Widget;

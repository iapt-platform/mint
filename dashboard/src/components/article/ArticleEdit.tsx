import { useState } from "react";

import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import { Button, Form, message, Result, Space, Tabs } from "antd";

import { get, put } from "../../request";
import {
  IArticleDataRequest,
  IArticleResponse,
} from "../../components/api/Article";
import LangSelect from "../../components/general/LangSelect";
import PublicitySelect from "../../components/studio/PublicitySelect";

import MDEditor from "@uiw/react-md-editor";
import ArticlePrevDrawer from "../../components/article/ArticlePrevDrawer";

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

interface IWidget {
  studioName?: string;
  articleId?: string;
  onReady?: Function;
  onChange?: Function;
}

const ArticleEditWidget = ({
  studioName,
  articleId,
  onReady,
  onChange,
}: IWidget) => {
  const intl = useIntl();
  const [unauthorized, setUnauthorized] = useState(false);
  const [readonly, setReadonly] = useState(false);
  const [content, setContent] = useState<string>();

  return unauthorized ? (
    <Result
      status="403"
      title="无权访问"
      subTitle="您无权访问该内容。您可能没有登录，或者内容的所有者没有给您所需的权限。"
      extra={<></>}
    />
  ) : (
    <>
      {readonly ? "只读" : undefined}
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          // TODO

          const request = {
            uid: articleId ? articleId : "",
            title: values.title,
            subtitle: values.subtitle,
            summary: values.summary,
            content: values.content,
            content_type: "markdown",
            status: values.status,
            lang: values.lang,
          };
          console.log("save", request);
          put<IArticleDataRequest, IArticleResponse>(
            `/v2/article/${articleId}`,
            request
          )
            .then((res) => {
              console.log("save response", res);
              if (res.ok) {
                if (typeof onChange !== "undefined") {
                  onChange(res.data);
                }
                message.success(intl.formatMessage({ id: "flashes.success" }));
              } else {
                message.error(res.message);
              }
            })
            .catch((e: IArticleResponse) => {
              message.error(e.message);
            });
        }}
        request={async () => {
          const res = await get<IArticleResponse>(`/v2/article/${articleId}`);
          console.log("article", res);
          let mTitle: string,
            mReadonly = false;
          if (res.ok) {
            mReadonly = res.data.role === "editor" ? false : true;
            setReadonly(mReadonly);
            mTitle = res.data.title;
            setContent(res.data.content);
          } else {
            setUnauthorized(true);
            mTitle = "无权访问";
          }
          if (typeof onReady !== "undefined") {
            onReady(mTitle, mReadonly, res.data.studio?.realName);
          }
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
                        {articleId ? (
                          <ArticlePrevDrawer
                            trigger={<Button>预览</Button>}
                            articleId={articleId}
                            content={content}
                          />
                        ) : undefined}
                      </Space>
                    }
                  >
                    <MDEditor onChange={(value) => setContent(value)} />
                  </Form.Item>
                </ProForm.Group>
              ),
            },
          ]}
        />
      </ProForm>
    </>
  );
};

export default ArticleEditWidget;

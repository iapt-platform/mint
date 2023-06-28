import { useRef, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Card, Form, message, Result, Space, Tabs } from "antd";

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
import ShareModal from "../../../components/share/ShareModal";
import { EResType } from "../../../components/share/Share";
import AddToAnthology from "../../../components/article/AddToAnthology";
import ReadonlyLabel from "../../../components/general/ReadonlyLabel";
import ArticlePrevDrawer from "../../../components/article/ArticlePrevDrawer";

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
  const [unauthorized, setUnauthorized] = useState(false);
  const [readonly, setReadonly] = useState(false);
  const [content, setContent] = useState<string>();

  return (
    <Card
      title={
        <Space>
          <GoBack to={`/studio/${studioname}/article/list`} title={title} />
          {readonly ? <ReadonlyLabel /> : undefined}
        </Space>
      }
      extra={
        <Space>
          {articleid ? (
            <AddToAnthology studioName={studioname} articleIds={[articleid]} />
          ) : undefined}
          {articleid ? (
            <ShareModal
              trigger={
                <Button icon={<TeamOutlined />}>
                  {intl.formatMessage({
                    id: "buttons.share",
                  })}
                </Button>
              }
              resId={articleid}
              resType={EResType.article}
            />
          ) : undefined}
          <Link to={`/article/article/${articleid}`} target="_blank">
            {intl.formatMessage({ id: "buttons.open.in.library" })}
          </Link>
          <ArticleTplMaker
            title={title}
            type="article"
            id={articleid}
            trigger={<Button>获取模版</Button>}
          />
        </Space>
      }
    >
      {unauthorized ? (
        <Result
          status="403"
          title="无权访问"
          subTitle="您无权访问该内容。您可能没有登录，或者内容的所有者没有给您所需的权限。"
          extra={<></>}
        />
      ) : (
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
            console.log("save", request);
            put<IArticleDataRequest, IArticleResponse>(
              `/v2/article/${articleid}`,
              request
            )
              .then((res) => {
                console.log("save response", res);
                if (res.ok) {
                  message.success(
                    intl.formatMessage({ id: "flashes.success" })
                  );
                } else {
                  message.error(res.message);
                }
              })
              .catch((e: IArticleResponse) => {
                message.error(e.message);
              });
          }}
          request={async () => {
            const res = await get<IArticleResponse>(`/v2/article/${articleid}`);
            console.log("article", res);
            if (res.ok) {
              const readonly = res.data.role === "editor" ? false : true;
              setReadonly(readonly);
              setTitle(res.data.title);
              setContent(res.data.content);
            } else {
              setUnauthorized(true);
              setTitle("无权访问");
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
                          {articleid ? (
                            <ArticlePrevDrawer
                              trigger={<Button>预览</Button>}
                              articleId={articleid}
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
      )}
    </Card>
  );
};

export default Widget;

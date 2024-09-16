import { useRef, useState } from "react";

import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormSwitch,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import { Alert, Button, Form, message, Result } from "antd";

import { get, put } from "../../request";
import {
  IArticleDataRequest,
  IArticleResponse,
} from "../../components/api/Article";
import LangSelect from "../../components/general/LangSelect";
import PublicitySelect from "../../components/studio/PublicitySelect";

import MDEditor from "@uiw/react-md-editor";
import ArticlePrevDrawer from "../../components/article/ArticlePrevDrawer";
import { IStudio } from "../auth/Studio";
import ArticleEditTools from "./ArticleEditTools";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IFormData {
  uid: string;
  title: string;
  subtitle: string;
  summary?: string | null;
  content?: string;
  content_type?: string;
  status: number;
  lang: string;
  to_tpl?: boolean;
}

interface IWidget {
  studioName?: string;
  articleId?: string;
  anthologyId?: string;
  resetButton?: "reset" | "cancel";
  onReady?: Function;
  onLoad?: Function;
  onChange?: Function;
  onCancel?: Function;
  onSubmit?: Function;
}

const ArticleEditWidget = ({
  studioName,
  articleId,
  anthologyId,
  resetButton = "reset",
  onReady,
  onLoad,
  onChange,
  onCancel,
  onSubmit,
}: IWidget) => {
  const intl = useIntl();
  const [unauthorized, setUnauthorized] = useState(false);
  const [readonly, setReadonly] = useState(false);
  const [content, setContent] = useState<string>();
  const [owner, setOwner] = useState<IStudio>();
  const formRef = useRef<ProFormInstance>();
  const [title, setTitle] = useState<string>();
  const user = useAppSelector(currentUser);

  return unauthorized ? (
    <Result
      status="403"
      title="无权访问"
      subTitle="您无权访问该内容。您可能没有登录，或者内容的所有者没有给您所需的权限。"
      extra={<></>}
    />
  ) : (
    <>
      {readonly ? (
        <Alert
          message={`该资源为只读，如果需要修改，请联络拥有者${owner?.nickName}分配权限。`}
          type="warning"
          closable
          action={
            <Button disabled size="small" type="text">
              详情
            </Button>
          }
        />
      ) : undefined}
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <span></span>
        <ArticleEditTools
          studioName={studioName}
          articleId={articleId}
          title={title}
        />
      </div>
      <ProForm<IFormData>
        formRef={formRef}
        submitter={{
          // 完全自定义整个区域
          render: (props, doms) => {
            console.log(props);
            return [
              <Button
                key="rest"
                onClick={() => {
                  if (resetButton === "reset") {
                    props.form?.resetFields();
                  } else {
                    if (typeof onCancel !== "undefined") {
                      onCancel();
                    }
                  }
                }}
              >
                {resetButton === "reset" ? "重置" : "取消"}
              </Button>,
              <Button
                type="primary"
                key="submit"
                onClick={() => props.form?.submit?.()}
              >
                提交
              </Button>,
            ];
          },
        }}
        onFinish={async (values: IFormData) => {
          const request: IArticleDataRequest = {
            uid: articleId ? articleId : "",
            title: values.title,
            subtitle: values.subtitle,
            summary: values.summary,
            content: values.content,
            content_type: "markdown",
            status: values.status,
            lang: values.lang,
            to_tpl: values.to_tpl,
            anthology_id: anthologyId,
          };
          const url = `/v2/article/${articleId}`;
          console.info("save url", url, request);
          put<IArticleDataRequest, IArticleResponse>(url, request)
            .then((res) => {
              console.debug("save response", res);
              if (res.ok) {
                if (typeof onChange !== "undefined") {
                  onChange(res.data);
                }
                if (typeof onSubmit !== "undefined") {
                  onSubmit(res.data);
                }
                formRef.current?.setFieldValue("content", res.data.content);
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
          const url = `/v2/article/${articleId}`;
          console.info("url", url);
          const res = await get<IArticleResponse>(url);
          console.log("article", res);
          let mTitle: string,
            mReadonly = false;
          if (res.ok) {
            setOwner(res.data.studio);
            mReadonly = res.data.role === "editor" ? false : true;
            setReadonly(mReadonly);
            mTitle = res.data.title;
            setContent(res.data.content);
            setTitle(res.data.title);
          } else {
            setUnauthorized(true);
            mTitle = "无权访问";
          }
          if (typeof onReady !== "undefined") {
            onReady(
              mTitle,
              mReadonly,
              res.data.studio?.realName,
              res.data.parent_uid
            );
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
            studio: res.data.studio,
          };
        }}
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
          <PublicitySelect
            width="md"
            disable={["public_no_list"]}
            readonly={
              user?.roles?.includes("basic") || owner?.roles?.includes("basic")
                ? true
                : false
            }
          />
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

        <Form.Item
          name="content"
          style={{ width: "100%" }}
          label={
            <>
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
            </>
          }
        >
          <MDEditor
            className="pcd_md_editor paper_zh"
            onChange={(value) => setContent(value)}
            height={450}
            minHeight={200}
            style={{ width: "100%" }}
          />
        </Form.Item>

        <ProForm.Group>
          <ProFormSwitch
            name="to_tpl"
            label="转换为模版"
            disabled={anthologyId ? false : true}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default ArticleEditWidget;

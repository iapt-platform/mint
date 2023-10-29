import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";
import { Alert, Space, message } from "antd";

import { get, post } from "../../request";
import {
  IAnthologyListResponse,
  IArticleCreateRequest,
  IArticleDataResponse,
  IArticleResponse,
} from "../api/Article";
import LangSelect from "../general/LangSelect";
import { useEffect, useRef, useState } from "react";

interface IFormData {
  title: string;
  lang: string;
  studio: string;
  anthologyId?: string;
  parentId?: string;
}

interface IWidget {
  studio?: string;
  anthologyId?: string;
  parentId?: string | null;
  compact?: boolean;
  onSuccess?: Function;
}
const ArticleCreateWidget = ({
  studio,
  anthologyId,
  parentId,
  compact = true,
  onSuccess,
}: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  const [parent, setParent] = useState<IArticleDataResponse>();
  console.log("parentId", parentId);
  useEffect(() => {
    if (parentId) {
      get<IArticleResponse>(`/v2/article/${parentId}`).then((json) => {
        console.log("article", json);

        if (json.ok) {
          setParent(json.data);
        }
      });
    }
  }, []);

  return (
    <Space direction="vertical">
      {parentId ? (
        <Alert
          message={`从文章 ${parent?.title} 创建子文章`}
          type="info"
          closable
        />
      ) : undefined}
      <ProForm<IFormData>
        formRef={formRef}
        onFinish={async (values: IFormData) => {
          console.log(values);
          if (typeof studio === "undefined") {
            return;
          }
          values.studio = studio;
          values.parentId = parentId ? parentId : undefined;
          const res = await post<IArticleCreateRequest, IArticleResponse>(
            `/v2/article`,
            values
          );
          console.log(res);
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
            if (typeof onSuccess !== "undefined") {
              onSuccess(res.data);
              formRef.current?.resetFields(["title"]);
            }
          } else {
            message.error(res.message);
          }
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="title"
            required
            label={intl.formatMessage({ id: "channel.name" })}
            rules={[
              {
                required: true,
                message: intl.formatMessage({
                  id: "channel.create.message.noname",
                }),
              },
            ]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <LangSelect />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            name={"anthologyId"}
            label={"加入文集"}
            hidden={compact}
            width={"md"}
            showSearch
            debounceTime={300}
            request={async ({ keyWords }) => {
              console.log("keyWord", keyWords);
              let url = `/v2/anthology?view=studio&view2=my&name=${studio}`;
              url += keyWords ? "&search=" + keyWords : "";
              const res = await get<IAnthologyListResponse>(url);
              const result = res.data.rows.map((item) => {
                return {
                  value: item.uid,
                  label: item.title,
                };
              });
              console.log("json", result);
              return result;
            }}
          />
        </ProForm.Group>
      </ProForm>
    </Space>
  );
};

export default ArticleCreateWidget;

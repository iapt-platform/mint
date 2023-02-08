import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Card, Form, message, Tabs } from "antd";

import { get, put } from "../../../request";
import {
  IAnthologyDataRequest,
  IAnthologyResponse,
} from "../../../components/api/Article";
import EditableTree from "../../../components/article/EditableTree";
import type { ListNodeData } from "../../../components/article/EditableTree";
import LangSelect from "../../../components/general/LangSelect";
import PublicitySelect from "../../../components/studio/PublicitySelect";
import GoBack from "../../../components/studio/GoBack";
import MDEditor from "@uiw/react-md-editor";

interface IFormData {
  title: string;
  subtitle: string;
  summary: string;
  lang: string;
  status: number;
}

const Widget = () => {
  const listdata: ListNodeData[] = [];
  const intl = useIntl();
  const [tocData, setTocData] = useState(listdata);
  const [title, setTitle] = useState("");
  const { studioname, anthology_id } = useParams(); //url 参数
  const [contentValue, setContentValue] = useState<string>("ddd");
  let treeList: ListNodeData[] = [];

  const edit = (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(values);

        const request: IAnthologyDataRequest = {
          title: values.title,
          subtitle: values.subtitle,
          summary: contentValue,
          article_list: treeList.map((item) => {
            return {
              article: item.key,
              title: item.title,
              level: item.level.toString(),
              children: 0,
            };
          }),
          status: values.status,
          lang: values.lang,
        };
        console.log(request);
        const res = await put<IAnthologyDataRequest, IAnthologyResponse>(
          `/v2/anthology/${anthology_id}`,
          request
        );
        console.log(res);
        if (res.ok) {
          message.success(
            intl.formatMessage({
              id: "flashes.success",
            })
          );
        } else {
          message.error(res.message);
        }
      }}
      request={async () => {
        const res = await get<IAnthologyResponse>(
          `/v2/anthology/${anthology_id}`
        );
        console.log("文集get", res);
        if (res.ok) {
          setTitle(res.data.title);

          if (res.data.article_list) {
            const toc: ListNodeData[] = res.data.article_list.map((item) => {
              return {
                key: item.article,
                title: item.title,
                level: parseInt(item.level),
              };
            });
            setTocData(toc);
            treeList = toc;
          }
          return {
            title: res.data.title,
            subtitle: res.data.subtitle,
            summary: res.data.summary,
            lang: res.data.lang,
            status: res.data.status,
          };
        } else {
          return {
            title: "",
            subtitle: "",
            summary: "",
            lang: "",
            status: 0,
          };
        }
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
        <PublicitySelect width="md" />
      </ProForm.Group>
      <ProForm.Group>
        <Form.Item
          name="summary"
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
  );
  return (
    <>
      <Card
        title={
          <GoBack to={`/studio/${studioname}/anthology/list`} title={title} />
        }
      >
        <Tabs
          items={[
            {
              key: "info",
              label: `基本信息`,
              children: edit,
            },
            {
              key: "toc",
              label: `目录`,
              children: (
                <EditableTree
                  treeData={tocData}
                  onChange={(data: ListNodeData[]) => {
                    treeList = data;
                  }}
                />
              ),
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;

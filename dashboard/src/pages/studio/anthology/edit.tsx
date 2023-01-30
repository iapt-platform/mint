import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Card, Col, message, Row, Tabs } from "antd";

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
  let treeList: ListNodeData[] = [];

  const edit = (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(values);

        const request: IAnthologyDataRequest = {
          title: values.title,
          subtitle: values.subtitle,
          summary: values.summary,
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
        const toc: ListNodeData[] = res.data.article_list.map((item) => {
          return {
            key: item.article,
            title: item.title,
            level: parseInt(item.level),
          };
        });
        setTocData(toc);
        treeList = toc;
        setTitle(res.data.title);
        return {
          title: res.data.title,
          subtitle: res.data.subtitle,
          summary: res.data.summary,
          lang: res.data.lang,
          status: res.data.status,
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
        <PublicitySelect width="md" />
      </ProForm.Group>

      <ProForm.Group>
        <ProFormTextArea
          name="summary"
          width="md"
          label={intl.formatMessage({
            id: "forms.fields.summary.label",
          })}
        />
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

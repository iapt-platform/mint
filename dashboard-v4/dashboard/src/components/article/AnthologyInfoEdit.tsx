import { Form, message } from "antd";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormSelect,
  ProFormText,
  RequestOptionsType,
} from "@ant-design/pro-components";
import MDEditor from "@uiw/react-md-editor";

import { get, put } from "../../request";
import {
  IAnthologyDataRequest,
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../api/Article";
import LangSelect from "../general/LangSelect";
import PublicitySelect from "../studio/PublicitySelect";
import { useState } from "react";
import { DefaultOptionType } from "antd/lib/select";
import { IApiResponseChannelList } from "../api/Channel";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IFormData {
  title: string;
  subtitle: string;
  summary?: string;
  lang: string;
  status: number;
  defaultChannel?: string;
}

interface IWidget {
  anthologyId?: string;
  studioName?: string;
  onLoad?: Function;
}
const AnthologyInfoEditWidget = ({
  studioName,
  anthologyId,
  onLoad,
}: IWidget) => {
  const intl = useIntl();
  const [channelOption, setChannelOption] = useState<DefaultOptionType[]>([]);
  const [currChannel, setCurrChannel] = useState<RequestOptionsType>();
  const [data, setData] = useState<IAnthologyDataResponse>();

  const user = useAppSelector(currentUser);

  return anthologyId ? (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        const url = `/v2/anthology/${anthologyId}`;
        console.log("url", url);
        console.log("values", values);
        const res = await put<IAnthologyDataRequest, IAnthologyResponse>(url, {
          title: values.title,
          subtitle: values.subtitle,
          summary: values.summary,
          status: values.status,
          lang: values.lang,
          default_channel: values.defaultChannel,
        });
        console.log(res);
        if (res.ok) {
          if (typeof onLoad !== "undefined") {
            onLoad(res.data);
          }
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
        const url = `/v2/anthology/${anthologyId}`;
        console.log("url", url);
        const res = await get<IAnthologyResponse>(url);
        console.log("文集get", res);
        if (res.ok) {
          setData(res.data);
          if (typeof onLoad !== "undefined") {
            onLoad(res.data);
          }
          if (res.data.default_channel) {
            const channel = {
              value: res.data.default_channel.id,
              label: res.data.default_channel.name,
            };
            setCurrChannel(channel);
            setChannelOption([channel]);
          }

          return {
            title: res.data.title,
            subtitle: res.data.subtitle,
            summary: res.data.summary ? res.data.summary : undefined,
            lang: res.data.lang,
            status: res.data.status,
            defaultChannel: res.data.default_channel?.id,
          };
        } else {
          return {
            title: "",
            subtitle: "",
            summary: "",
            lang: "",
            status: 0,
            defaultChannel: "",
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
        <PublicitySelect
          width="md"
          disable={["public_no_list"]}
          readonly={
            user?.roles?.includes("basic") ||
            data?.studio.roles?.includes("basic")
              ? true
              : false
          }
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormSelect
          options={channelOption}
          width="md"
          name="defaultChannel"
          label={"默认版本"}
          showSearch
          debounceTime={300}
          request={async ({ keyWords }) => {
            console.log("keyWord", keyWords);
            if (typeof keyWords === "undefined") {
              return currChannel ? [currChannel] : [];
            }
            const url = `/v2/channel?view=studio&name=${studioName}`;
            console.log("url", url);
            const json = await get<IApiResponseChannelList>(url);
            const textbookList = json.data.rows.map((item) => {
              return {
                value: item.uid,
                label: `${item.studio.nickName}/${item.name}`,
              };
            });
            console.log("json", textbookList);
            return textbookList;
          }}
        />
      </ProForm.Group>
      <ProForm.Group>
        <Form.Item
          name="summary"
          label={intl.formatMessage({ id: "forms.fields.summary.label" })}
        >
          <MDEditor />
        </Form.Item>
      </ProForm.Group>
    </ProForm>
  ) : (
    <></>
  );
};

export default AnthologyInfoEditWidget;

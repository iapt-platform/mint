import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Alert, message } from "antd";

import {
  IApiResponseChannel,
  IApiResponseChannelData,
} from "../../components/api/Channel";
import { get, put } from "../../request";
import ChannelTypeSelect from "../../components/channel/ChannelTypeSelect";
import LangSelect from "../../components/general/LangSelect";
import PublicitySelect from "../../components/studio/PublicitySelect";
import { useState } from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IFormData {
  name: string;
  type: string;
  lang: string;
  summary: string;
  status: number;
  studio: string;
  isSystem: boolean;
}
interface IWidget {
  studioName?: string;
  channelId?: string;
  onLoad?: Function;
}
const EditWidget = ({ studioName, channelId, onLoad }: IWidget) => {
  const intl = useIntl();
  const [isSystem, setIsSystem] = useState<Boolean>();
  const [data, setData] = useState<IApiResponseChannelData>();

  const user = useAppSelector(currentUser);
  return (
    <>
      {isSystem ? (
        <Alert type="warning" message={"此版本为系统建立。不能修改，删除。"} />
      ) : (
        <></>
      )}
      <ProForm<IFormData>
        submitter={{
          resetButtonProps: { disabled: isSystem ? true : false },
          submitButtonProps: { disabled: isSystem ? true : false },
        }}
        onFinish={async (values: IFormData) => {
          console.log(values);
          const res = await put(`/v2/channel/${channelId}`, values);
          console.log(res);
          message.success(intl.formatMessage({ id: "flashes.success" }));
        }}
        formKey="channel_edit"
        request={async () => {
          const res = await get<IApiResponseChannel>(
            `/v2/channel/${channelId}`
          );
          if (res.ok === false) {
            return {
              name: "",
              type: "",
              lang: "",
              summary: "",
              status: 0,
              studio: "",
              isSystem: true,
            };
          }
          setData(res.data);
          if (typeof onLoad !== "undefined") {
            onLoad(res.data);
          }
          setIsSystem(res.data.is_system);
          return {
            name: res.data.name,
            type: res.data.type,
            lang: res.data.lang,
            summary: res.data.summary,
            status: res.data.status,
            studio: studioName ? studioName : "",
            isSystem: res.data.is_system,
          };
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="name"
            readonly={isSystem ? true : false}
            required
            label={intl.formatMessage({ id: "channel.name" })}
            rules={[
              {
                required: true,
              },
            ]}
          />
        </ProForm.Group>

        <ProForm.Group>
          <ChannelTypeSelect readonly={isSystem ? true : false} />
          <LangSelect readonly={isSystem ? true : false} />
        </ProForm.Group>
        <ProForm.Group>
          <PublicitySelect
            readonly={
              isSystem || user?.roles?.includes("basic") || data?.status === 5
                ? true
                : false
            }
            disable={["public_no_list"]}
          />
        </ProForm.Group>

        <ProForm.Group>
          <ProFormTextArea
            readonly={isSystem ? true : false}
            width="md"
            name="summary"
            label="简介"
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default EditWidget;

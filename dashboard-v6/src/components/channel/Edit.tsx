import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormSelect,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Alert, message } from "antd";

import {
  fetchChannel,
  SOURCE_TYPE_OPTIONS,
  updateChannel,
  type Channel,
  type ChannelInput,
  type TChannelSourceType,
} from "../../api/channel";
import ChannelTypeSelect from "./ChannelTypeSelect";
import LangSelect from "../general/LangSelect";
import PublicitySelect from "../studio/PublicitySelect";
import { useState } from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IFormData {
  name: string;
  type: string;
  lang: string;
  summary: string;
  status: number;
  source_type?: TChannelSourceType | null;
  source_id?: string | null;
}
interface IWidget {
  channelId?: string;
  onLoad?: (data: Channel) => void;
}
/**
 * channel 基本信息。没有「改信息」权限（abilities.update）时整张表只读——
 * 系统 channel、只读成员、共享权限不足都属于这种。
 */
const EditWidget = ({ channelId, onLoad }: IWidget) => {
  const intl = useIntl();
  const [isSystem, setIsSystem] = useState<boolean>();
  // 默认只读，取到 abilities 再放开，避免先可编辑、再变只读
  const [readonly, setReadonly] = useState<boolean>(true);
  const [data, setData] = useState<Channel>();

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
          resetButtonProps: { disabled: readonly },
          submitButtonProps: { disabled: readonly },
        }}
        onFinish={async (values: IFormData) => {
          if (!channelId) {
            return;
          }
          // 只改动过的字段也无妨：PATCH 只认传了的字段。失败由 unwrap 统一弹提示
          await updateChannel(channelId, {
            ...values,
            source_type: values.source_type ?? undefined,
          } as ChannelInput);
          message.success(intl.formatMessage({ id: "flashes.success" }));
        }}
        formKey="channel_edit"
        request={async () => {
          const channel = channelId ? await fetchChannel(channelId) : undefined;
          if (!channel) {
            return { name: "", type: "", lang: "", summary: "", status: 0 };
          }
          setData(channel);
          onLoad?.(channel);
          setIsSystem(channel.is_system);
          setReadonly(!channel.abilities?.update);
          return {
            name: channel.name ?? "",
            type: channel.type ?? "",
            lang: channel.lang ?? "",
            summary: channel.summary ?? "",
            status: channel.status ?? 0,
            source_type: (channel.source_type ??
              null) as TChannelSourceType | null,
            source_id: channel.source_id,
          };
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="name"
            readonly={readonly}
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
          <ChannelTypeSelect readonly={readonly} />
          <LangSelect readonly={readonly} />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            options={SOURCE_TYPE_OPTIONS.map((value) => ({
              value,
              label: intl.formatMessage({ id: `channel.source_type.${value}` }),
            }))}
            initialValue="original"
            width="xs"
            name="source_type"
            allowClear={false}
            label={intl.formatMessage({ id: "channel.source_type" })}
          />
          <ProFormText
            width="md"
            name="source_id"
            allowClear={true}
            label={intl.formatMessage({ id: "channel.source_id" })}
          />
        </ProForm.Group>
        <ProForm.Group>
          <PublicitySelect
            readonly={
              readonly || user?.roles?.includes("basic") || data?.status === 5
            }
          />
        </ProForm.Group>

        <ProForm.Group>
          <ProFormTextArea
            readonly={readonly}
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

import { ProFormCascader } from "@ant-design/pro-components";
import { message } from "antd";

import { get } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";

interface IOption {
  value: string;
  label: string;
}

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  channelId?: string;
  name?: string;
  tooltip?: string;
  label?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  onSelect?: Function;
}
const ChannelSelectWidget = ({
  width = "md",
  channelId,
  name = "channel",
  tooltip,
  label,
  parentChannelId,
  parentStudioId,
  onSelect,
}: IWidget) => {
  return (
    <ProFormCascader
      width={width}
      name={name}
      tooltip={tooltip}
      label={label}
      request={async ({ keyWords }) => {
        console.log("keyWord", keyWords);
        const json = await get<IApiResponseChannelList>(
          `/v2/channel?view=user-edit&key=${keyWords}`
        );
        if (json.ok) {
          //获取studio list
          let studio = new Map<string, string>();
          for (const iterator of json.data.rows) {
            studio.set(iterator.studio.id, iterator.studio.nickName);
          }
          let channels: IOption[] = [{ value: "", label: "通用于此Studio" }];
          if (typeof parentChannelId === "string") {
            channels.push({ value: parentChannelId, label: "仅此版本" });
          }
          studio.forEach((value, key, map) => {
            const node = {
              value: key,
              label: value,
              children: json.data.rows
                .filter((value) => value.studio.id === key)
                .map((item) => {
                  return { value: item.uid, label: item.name };
                }),
            };
            channels.push(node);
          });

          console.log("json", channels);
          return channels;
        } else {
          message.error(json.message);
          return [];
        }
      }}
      fieldProps={{}}
    />
  );
};

export default ChannelSelectWidget;

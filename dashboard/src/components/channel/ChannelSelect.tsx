import { ProFormCascader } from "@ant-design/pro-components";
import { message, Select } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";
import { IStudio } from "../auth/StudioName";

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
  onSelect?: Function;
}
const Widget = ({
  width = "md",
  channelId,
  name = "channel",
  tooltip,
  label,
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
          let channels: IOption[] = [];

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

export default Widget;

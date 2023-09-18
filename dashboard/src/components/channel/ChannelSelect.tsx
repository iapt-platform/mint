import { ProFormCascader } from "@ant-design/pro-components";
import { message } from "antd";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

import { get } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";
import { IStudio } from "../auth/StudioName";

interface IOption {
  value: string;
  label?: string;
  lang?: string;
  children?: IOption[];
}

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  channelId?: string;
  name?: string;
  tooltip?: string;
  label?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  placeholder?: string;
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
  placeholder,
  onSelect,
}: IWidget) => {
  const user = useAppSelector(currentUser);
  return (
    <ProFormCascader
      width={width}
      name={name}
      tooltip={tooltip}
      label={label}
      placeholder={placeholder}
      request={async ({ keyWords }) => {
        console.log("keyWord", keyWords);
        const json = await get<IApiResponseChannelList>(
          `/v2/channel?view=user-edit&key=${keyWords}`
        );
        if (json.ok) {
          //获取studio list
          let studio = new Map<string, string>();
          for (const iterator of json.data.rows) {
            studio.set(
              iterator.studio.id,
              iterator.studio.nickName ? iterator.studio.nickName : ""
            );
          }
          let channels: IOption[] = [];
          console.log("parentStudioId", parentStudioId);
          if (user && user.id === parentStudioId) {
            channels.push({ value: "", label: "通用于此Studio" });
          }
          if (typeof parentChannelId === "string") {
            channels.push({ value: parentChannelId, label: "仅此版本" });
          }

          if (user) {
            //自己的 studio
            channels.push({
              value: user.id,
              label: user.realName,
              children: json.data.rows
                .filter((value) => value.studio.id === user.id)
                .map((item) => {
                  return { value: item.uid, label: item.name, lang: item.lang };
                }),
            });
          }

          let arrStudio: IStudio[] = [];
          studio.forEach((value, key, map) => {
            arrStudio.push({ id: key, nickName: value });
          });

          const others: IOption[] = arrStudio
            .sort((a, b) =>
              a.nickName && b.nickName ? (a.nickName > b.nickName ? 1 : -1) : 0
            )
            .filter((value) => value.id !== user?.id)
            .map((item) => {
              const node = {
                value: item.id,
                label: item.nickName,
                children: json.data.rows
                  .filter((value) => value.studio.id === item.id)
                  .map((item) => {
                    return {
                      value: item.uid,
                      label: item.name,
                      lang: item.lang,
                    };
                  }),
              };
              return node;
            });
          channels = [...channels, ...others];

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
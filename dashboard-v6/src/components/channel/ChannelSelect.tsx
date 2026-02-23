import { ProFormCascader } from "@ant-design/pro-components";
import { message } from "antd";
import { useAppSelector } from "../../../src/hooks";
import { currentUser } from "../../../src/reducers/current-user";

import { get } from "../../../src/request";
import type { IApiResponseChannelList } from "../../../src/api/Channel";

import { useIntl } from "react-intl";
import type { IStudio } from "../../../src/api/Auth";

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
  allowClear?: boolean;
  parentChannelId?: string;
  parentStudioId?: string;
  placeholder?: string;
  onSelect?: (selected: string[]) => void;
}
const ChannelSelectWidget = ({
  width = "md",
  name = "channel",
  tooltip,
  label,
  parentChannelId,
  parentStudioId,
  placeholder,
  allowClear = true,
}: IWidget) => {
  const user = useAppSelector(currentUser);
  const intl = useIntl();
  return (
    <ProFormCascader
      width={width}
      name={name}
      tooltip={tooltip}
      label={label}
      allowClear={allowClear}
      placeholder={placeholder}
      request={async ({ keyWords }) => {
        console.debug("keyWord", keyWords);
        const url = `/api/v2/channel?view=user-edit&key=${keyWords}`;
        console.info("ChannelSelect api request", url);
        const json = await get<IApiResponseChannelList>(url);
        console.debug("ChannelSelect api response", json);
        if (json.ok) {
          //获取studio list
          const studio = new Map<string, string>();
          for (const iterator of json.data.rows) {
            studio.set(
              iterator.studio.id,
              iterator.studio.nickName ? iterator.studio.nickName : ""
            );
          }
          let channels: IOption[] = [];

          if (user && user.id === parentStudioId) {
            if (!user.roles?.includes("basic")) {
              channels.push({
                value: "",
                label: intl.formatMessage({
                  id: "term.general-in-studio",
                }),
              });
            }
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

          const arrStudio: IStudio[] = [];
          studio.forEach((value, key) => {
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

          console.debug("ChannelSelect json", channels);
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

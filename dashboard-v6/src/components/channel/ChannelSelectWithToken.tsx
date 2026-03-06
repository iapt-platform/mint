import { useState } from "react";
import { Button, Input, Space, Tooltip, Typography } from "antd";
import {
  FolderOpenOutlined,
  CheckCircleTwoTone,
  LoadingOutlined,
  WarningTwoTone,
} from "@ant-design/icons";

import type { IChannel, TChannelType } from "../../api/channel";
import { post } from "../../../src/request";
import ChannelTableModal from "./ChannelTableModal";

import type {
  IPayload,
  ITokenCreate,
  ITokenCreateResponse,
  TPower,
} from "../../../src/api/token";

const { Text } = Typography;

interface IData {
  value: string;
  label: string;
}

interface IWidget {
  channelsId?: string[];
  type?: TChannelType;
  book?: number;
  para?: number;
  power?: TPower;
  onChange?: (channel?: string | null) => void;
}
const ChannelSelectWithToken = ({
  type,
  book,
  para,
  power,
  onChange,
}: IWidget) => {
  const [curr, setCurr] = useState<IData>();
  const [access, setAccess] = useState<boolean>();
  const [loading, setLoading] = useState(false);
  return (
    <Space>
      <Input
        allowClear
        value={curr?.label}
        placeholder="选择一个版本"
        onChange={(event) => {
          if (event.target.value.trim().length === 0) {
            setCurr(undefined);
            setAccess(undefined);
            if (onChange) {
              onChange(undefined);
            }
          }
        }}
      />
      <ChannelTableModal
        chapter={book && para ? { book: book, paragraph: para } : undefined}
        channelType={type}
        trigger={<Button icon={<FolderOpenOutlined />} type="text" />}
        onSelect={(channel: IChannel) => {
          setCurr({ value: channel.id, label: channel.name });
          //验证权限
          if (power) {
            setLoading(true);
            const payload: IPayload[] = [];
            payload.push({
              res_id: channel.id,
              res_type: "channel",
              power: power,
            });
            const url = "/api/v2/access-token";
            const values = { payload: payload };
            console.info("token api request", url, values);
            post<ITokenCreate, ITokenCreateResponse>(url, values)
              .then((json) => {
                console.info("token api response", json);
                if (json.ok) {
                  if (json.data.count > 0) {
                    setAccess(true);
                  }
                }
              })
              .finally(() => setLoading(false));
          }

          if (onChange) {
            onChange(channel.id + (power ? "@" + power : ""));
          }
        }}
      />
      <Text type="secondary">{power}</Text>
      {loading ? (
        <LoadingOutlined />
      ) : typeof access !== "undefined" ? (
        access ? (
          <CheckCircleTwoTone twoToneColor="#52c41a" />
        ) : (
          <Tooltip title="无法获取指定的权限">
            <WarningTwoTone twoToneColor="#eb2f96" />
          </Tooltip>
        )
      ) : (
        <></>
      )}
    </Space>
  );
};

export default ChannelSelectWithToken;

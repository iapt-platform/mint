import { Button, message, Segmented, Typography } from "antd";
import type { SegmentedValue } from "antd/lib/segmented";
import { useEffect, useState } from "react";
import { CopyOutlined } from "@ant-design/icons";

import { useIntl } from "react-intl";
import type { ArticleType } from "./Article";
import { post } from "../../request";
import type {
  IPayload,
  ITokenCreate,
  ITokenCreateResponse,
  TPower,
} from "../../api/token";
const { Text } = Typography;

interface IWidget {
  channelId?: string;
  articleId?: string;
  type?: ArticleType;
}
const DictInfoCopyRef = ({ channelId, articleId, type }: IWidget) => {
  const [text, setText] = useState("");
  const [power, setPower] = useState<TPower>("readonly");
  const intl = useIntl();

  useEffect(() => {
    if (!channelId || !articleId || !type) {
      console.error("token", channelId, articleId, type);
      return;
    }
    const id = articleId.split("-");
    if (!channelId || !id || id.length < 2) {
      console.error("channels or book or para is undefined", channelId, id);
      return;
    }
    const _book = id[0];
    const _para = id[1];
    const payload: IPayload[] = [];
    payload.push({
      res_id: channelId,
      res_type: "channel",
      book: parseInt(_book),
      para_start: parseInt(_para),
      para_end: parseInt(_para) + 100,
      power: power,
    });
    const url = "/v2/access-token";
    const values = { payload: payload };
    console.info("token api request", url, values);
    post<ITokenCreate, ITokenCreateResponse>(url, values).then((json) => {
      console.info("token api response", json);
      if (json.ok) {
        setText(json.data.rows[0].token);
      }
    });
  }, [articleId, channelId, power, type]);
  return (
    <div>
      <div style={{ textAlign: "center", padding: 20 }}>
        <Segmented
          options={["readonly", "edit"]}
          onChange={(value: SegmentedValue) => {
            setPower(value as TPower);
          }}
        />
      </div>
      <div>
        <Text>{text}</Text>
      </div>

      <div style={{ textAlign: "center", padding: 20 }}>
        <Button
          type="primary"
          style={{ width: 200 }}
          icon={<CopyOutlined />}
          onClick={() => {
            navigator.clipboard.writeText(text).then(() => {
              message.success("链接地址已经拷贝到剪贴板");
            });
          }}
        >
          {intl.formatMessage({
            id: "buttons.copy",
          })}
        </Button>
      </div>
    </div>
  );
};

export default DictInfoCopyRef;

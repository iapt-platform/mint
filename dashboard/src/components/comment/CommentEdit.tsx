import { useState } from "react";
import { useIntl } from "react-intl";
import { Button, Card } from "antd";
import { Input, message } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { IComment } from "./CommentItem";
import { put } from "../../request";
import { ICommentRequest, ICommentResponse } from "../api/Comment";

const { TextArea } = Input;

interface IWidget {
  data: IComment;
}
const Widget = ({ data }: IWidget) => {
  const intl = useIntl();
  const [value, setValue] = useState(data.content);

  const [saving, setSaving] = useState<boolean>(false);

  const save = () => {
    setSaving(true);
    put<ICommentRequest, ICommentResponse>(`/v2/comment/${data.id}`, {
      content: value,
    })
      .then((json) => {
        console.log(json);
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => {
        setSaving(false);
        console.error("catch", e);
        message.error(e.message);
      });
  };

  return (
    <div>
      <Card
        title={<span>{data.user.nickName}</span>}
        extra={
          <Button shape="circle" size="small">
            xxx
          </Button>
        }
        style={{ width: "auto" }}
      >
        <TextArea
          rows={4}
          showCount
          maxLength={2048}
          value={value}
          onChange={(e) => setValue(e.target.value)}
        />

        <div>
          <Button
            type="primary"
            icon={<SaveOutlined />}
            loading={saving}
            onClick={() => save()}
          >
            Save
          </Button>
        </div>
      </Card>
    </div>
  );
};

export default Widget;

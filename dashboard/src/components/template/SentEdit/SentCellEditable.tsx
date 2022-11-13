import { Button, message, Typography } from "antd";
import { SaveOutlined } from "@ant-design/icons";
import TextArea from "antd/lib/input/TextArea";
import { useState } from "react";
import { useIntl } from "react-intl";
import { put } from "../../../request";
import {
  IErrorResponse,
  ISentenceRequest,
  ISentenceResponse,
} from "../../api/Corpus";
import { ISentence } from "../SentEdit";
const { Text } = Typography;

interface ISentCellEditable {
  data: ISentence;
}
const Widget = ({ data }: ISentCellEditable) => {
  const intl = useIntl();
  const [value, setValue] = useState(data.content);
  const [saving, setSaving] = useState<boolean>(false);
  const save = () => {
    setSaving(true);
    put<ISentenceRequest, ISentenceResponse>(
      `/v2/sentence/${data.book}_${data.para}_${data.wordStart}_${data.wordEnd}_${data.channel.id}`,
      {
        book: data.book,
        para: data.para,
        wordStart: data.wordStart,
        wordEnd: data.wordEnd,
        channel: data.channel.id,
        content: value,
      }
    )
      .then((json) => {
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
      <TextArea
        value={value}
        onChange={(e) => setValue(e.target.value)}
        placeholder="请输入"
        autoSize={{ minRows: 3, maxRows: 5 }}
      />
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <div>
          <span>
            <Text keyboard>esc</Text>=
            <Button size="small" type="link">
              cancel
            </Button>
          </span>
          <span>
            <Text keyboard>enter</Text>=
            <Button size="small" type="link">
              new line
            </Button>
          </span>
        </div>
        <div>
          <Text keyboard>Ctrl/⌘</Text>➕<Text keyboard>enter</Text>=
          <Button
            size="small"
            type="primary"
            icon={<SaveOutlined />}
            loading={saving}
            onClick={() => save()}
          >
            Save
          </Button>
        </div>
      </div>
    </div>
  );
};

export default Widget;

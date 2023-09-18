import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Button, message, Typography } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { post, put } from "../../../request";
import {
  ISentencePrRequest,
  ISentencePrResponse,
  ISentenceRequest,
  ISentenceResponse,
} from "../../api/Corpus";
import { ISentence } from "../SentEdit";
import TermTextArea from "../../general/TermTextArea";
import { useAppSelector } from "../../../hooks";
import { wordList } from "../../../reducers/sent-word";
import Builder from "../Builder/Builder";

const { Text } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  onSave?: Function;
  onClose?: Function;
  onCreate?: Function;
}
const SentCellEditableWidget = ({
  data,
  onSave,
  onClose,
  onCreate,
  isPr = false,
}: IWidget) => {
  const intl = useIntl();
  const [value, setValue] = useState(data.content);
  const [saving, setSaving] = useState<boolean>(false);
  const [termList, setTermList] = useState<string[]>();
  const sentWords = useAppSelector(wordList);

  useEffect(() => {
    const sentId = `${data.book}-${data.para}-${data.wordStart}-${data.wordEnd}`;
    setTermList(sentWords.find((value) => value.sentId === sentId)?.words);
  }, [data.book, data.para, data.wordEnd, data.wordStart, sentWords]);

  const savePr = () => {
    setSaving(true);
    if (!value) {
      return;
    }
    post<ISentencePrRequest, ISentencePrResponse>(`/v2/sentpr`, {
      book: data.book,
      para: data.para,
      begin: data.wordStart,
      end: data.wordEnd,
      channel: data.channel.id,
      text: value,
    })
      .then((json) => {
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onCreate !== "undefined") {
            onCreate();
          }
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

  const save = () => {
    setSaving(true);
    let url = `/v2/sentence/${data.book}_${data.para}_${data.wordStart}_${data.wordEnd}_${data.channel.id}`;
    url += "?mode=edit&html=true";
    console.log("save url", url);
    const body = {
      book: data.book,
      para: data.para,
      wordStart: data.wordStart,
      wordEnd: data.wordEnd,
      channel: data.channel.id,
      content: value,
      channels: data.translationChannels?.join(),
    };
    put<ISentenceRequest, ISentenceResponse>(url, body)
      .then((json) => {
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onSave !== "undefined") {
            const newData: ISentence = {
              id: json.data.id,
              content: json.data.content,
              html: json.data.html,
              book: json.data.book,
              para: json.data.paragraph,
              wordStart: json.data.word_start,
              wordEnd: json.data.word_end,
              editor: json.data.editor,
              channel: json.data.channel,
              updateAt: json.data.updated_at,
            };
            onSave(newData);
          }
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
    <Typography.Paragraph style={{ width: "100%" }}>
      <TermTextArea
        value={value ? value : ""}
        menuOptions={termList}
        onChange={(value: string) => {
          setValue(value);
        }}
        placeholder="请输入"
        onClose={() => {
          if (typeof onClose !== "undefined") {
            onClose();
          }
        }}
        onSave={(value: string) => {
          setValue(value);
          isPr ? savePr() : save();
        }}
      />
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <div>
          <span>
            <Text keyboard>esc</Text>=
            <Button
              size="small"
              type="link"
              onClick={(e) => {
                if (typeof onClose !== "undefined") {
                  onClose();
                }
              }}
            >
              {intl.formatMessage({ id: "buttons.cancel" })}
            </Button>
          </span>
          <span>
            <Text keyboard>enter</Text>=
            <Button size="small" type="link">
              new line
            </Button>
          </span>
          <Text keyboard style={{ cursor: "pointer" }}>
            <Builder trigger={"<t>"} />
          </Text>
        </div>
        <div>
          <Text keyboard>Ctrl/⌘</Text>➕<Text keyboard>enter</Text>=
          <Button
            size="small"
            type="primary"
            icon={<SaveOutlined />}
            loading={saving}
            onClick={() => (isPr ? savePr() : save())}
          >
            {intl.formatMessage({ id: "buttons.save" })}
          </Button>
        </div>
      </div>
    </Typography.Paragraph>
  );
};

export default SentCellEditableWidget;
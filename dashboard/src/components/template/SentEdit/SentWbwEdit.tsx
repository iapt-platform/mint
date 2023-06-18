import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Button, message } from "antd";
import { EyeOutlined } from "@ant-design/icons";

import { put } from "../../../request";
import { ISentenceRequest, ISentenceResponse } from "../../api/Corpus";
import { ISentence } from "../SentEdit";
import { WbwSentCtl } from "../WbwSent";
import { IWbw } from "../Wbw/WbwWord";

interface IWidget {
  data: ISentence;
  onSave?: Function;
  onClose?: Function;
  onCreate?: Function;
}
const SentWbwEditWidget = ({ data, onSave, onClose, onCreate }: IWidget) => {
  const intl = useIntl();
  const [saving, setSaving] = useState<boolean>(false);
  const [wbwData, setWbwData] = useState<IWbw[]>([]);

  useEffect(() => {
    if (data.contentType === "json") {
      setWbwData(JSON.parse(data.content));
    }
  }, [data.content, data.contentType]);

  const save = (content: string) => {
    setSaving(true);
    put<ISentenceRequest, ISentenceResponse>(
      `/v2/sentence/${data.book}_${data.para}_${data.wordStart}_${data.wordEnd}_${data.channel.id}`,
      {
        book: data.book,
        para: data.para,
        wordStart: data.wordStart,
        wordEnd: data.wordEnd,
        channel: data.channel.id,
        content: content,
        contentType: data.contentType,
      }
    )
      .then((json) => {
        console.log(json);
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onSave !== "undefined") {
            const newData: ISentence = {
              id: json.data.id,
              content: json.data.content,
              contentType: json.data.content_type,
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
    <div>
      <WbwSentCtl
        book={data.book}
        para={data.para}
        wordStart={data.wordStart}
        wordEnd={data.wordEnd}
        data={wbwData}
        refreshable={true}
        display="list"
        layoutDirection="v"
        fields={{
          real: true,
          meaning: true,
          factors: false,
          factorMeaning: false,
          case: true,
        }}
        channelId={data.channel.id}
        channelType={data.channel.type}
        onChange={(data: IWbw[]) => {
          save(JSON.stringify(data));
        }}
      />

      <div>
        <Button
          size="small"
          type="primary"
          icon={<EyeOutlined />}
          loading={saving}
          onClick={() => {
            if (typeof onClose !== "undefined") {
              onClose();
            }
          }}
        >
          {intl.formatMessage({ id: "buttons.preview" })}
        </Button>
      </div>
    </div>
  );
};

export default SentWbwEditWidget;

import { useState } from "react";
import { useIntl } from "react-intl";
import { Button, message } from "antd";
import { CheckOutlined } from "@ant-design/icons";

import { put } from "../../../request";
import { ISentenceRequest, ISentenceResponse } from "../../api/Corpus";
import { ISentence } from "../SentEdit";
import store from "../../../store";
import { accept } from "../../../reducers/accept-pr";

interface IWidget {
  data: ISentence;
  onAccept?: Function;
}
const Widget = ({ data, onAccept }: IWidget) => {
  const intl = useIntl();

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
        content: data.content,
        prEditor: data.editor.id,
        prId: data.id,
        prEditAt: data.updateAt,
      }
    )
      .then((json) => {
        console.log(json);
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));

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
            acceptor: json.data.acceptor,
            prEditAt: json.data.pr_edit_at,
            suggestionCount: json.data.suggestionCount,
          };
          store.dispatch(accept(newData));
          if (typeof onAccept !== "undefined") {
            onAccept(newData);
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
    <Button
      size="small"
      type="text"
      icon={<CheckOutlined />}
      loading={saving}
      onClick={() => save()}
    />
  );
};

export default Widget;

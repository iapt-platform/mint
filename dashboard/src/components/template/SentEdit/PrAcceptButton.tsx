import { useState } from "react";
import { useIntl } from "react-intl";
import { Button, message, Tooltip } from "antd";
import { CheckOutlined } from "@ant-design/icons";

import { put } from "../../../request";
import { ISentenceRequest, ISentenceResponse } from "../../api/Corpus";
import { ISentence, toISentence } from "../SentEdit";
import store from "../../../store";
import { accept } from "../../../reducers/accept-pr";

interface IWidget {
  data: ISentence;
  onAccept?: Function;
}
const PrAcceptButtonWidget = ({ data, onAccept }: IWidget) => {
  const intl = useIntl();

  const [saving, setSaving] = useState<boolean>(false);

  const save = () => {
    setSaving(true);
    const url = `/v2/sentence/${data.book}_${data.para}_${data.wordStart}_${data.wordEnd}_${data.channel.id}`;
    const prData = {
      book: data.book,
      para: data.para,
      wordStart: data.wordStart,
      wordEnd: data.wordEnd,
      channel: data.channel.id,
      content: data.content,
      prEditor: data.editor.id,
      prId: data.id,
      prUuid: data.uid,
      prEditAt: data.updateAt,
    };
    console.debug("pr accept url", url, prData);
    put<ISentenceRequest, ISentenceResponse>(url, prData)
      .then((json) => {
        console.log(json);
        setSaving(false);

        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));

          const newData: ISentence = toISentence(json.data);

          store.dispatch(accept([newData]));
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
    <Tooltip title="采纳此修改建议">
      <Button
        size="small"
        type="text"
        icon={<CheckOutlined />}
        loading={saving}
        onClick={() => save()}
      />
    </Tooltip>
  );
};

export default PrAcceptButtonWidget;

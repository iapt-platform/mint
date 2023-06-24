import { useEffect, useState } from "react";
import { IntlShape, useIntl } from "react-intl";
import { Button, message } from "antd";
import { EyeOutlined } from "@ant-design/icons";

import { put } from "../../../request";
import { ISentenceRequest, ISentenceResponse } from "../../api/Corpus";
import { ISentence } from "../SentEdit";
import { WbwSentCtl } from "../WbwSent";
import { IWbw } from "../Wbw/WbwWord";
import store from "../../../store";
import { statusChange } from "../../../reducers/net-status";

export const sentSave = (sent: ISentence, intl: IntlShape) => {
  store.dispatch(statusChange({ status: "loading" }));
  put<ISentenceRequest, ISentenceResponse>(
    `/v2/sentence/${sent.book}_${sent.para}_${sent.wordStart}_${sent.wordEnd}_${sent.channel.id}`,
    {
      book: sent.book,
      para: sent.para,
      wordStart: sent.wordStart,
      wordEnd: sent.wordEnd,
      channel: sent.channel.id,
      content: sent.content,
      contentType: sent.contentType,
    }
  )
    .then((json) => {
      console.log(json);
      if (json.ok) {
        store.dispatch(
          statusChange({
            status: "success",
            message: intl.formatMessage({ id: "flashes.success" }),
          })
        );
      } else {
        message.error(json.message);
        store.dispatch(
          statusChange({
            status: "fail",
            message: json.message,
          })
        );
      }
    })
    .catch((e) => {
      console.error("catch", e);
      message.error(e.message);
      store.dispatch(
        statusChange({
          status: "fail",
          message: e.message,
        })
      );
    });
};

interface IWidget {
  data: ISentence;
  onSave?: Function;
  onClose?: Function;
}
const SentWbwEditWidget = ({ data, onSave, onClose }: IWidget) => {
  const intl = useIntl();
  const [wbwData, setWbwData] = useState<IWbw[]>([]);

  useEffect(() => {
    if (data.contentType === "json") {
      setWbwData(JSON.parse(data.content));
    }
  }, [data.content, data.contentType]);

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
          factorMeaning2: true,
          case: false,
        }}
        channelId={data.channel.id}
        channelType={data.channel.type}
        onChange={(wbwData: IWbw[]) => {
          let newSent = data;
          newSent.content = JSON.stringify(wbwData);
          sentSave(newSent, intl);
          if (typeof onSave !== "undefined") {
            onSave(newSent);
          }
        }}
      />

      <div>
        <Button
          size="small"
          type="primary"
          icon={<EyeOutlined />}
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

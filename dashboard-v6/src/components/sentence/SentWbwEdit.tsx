import { useMemo } from "react";
import { useIntl } from "react-intl";
import { Button, message } from "antd";
import { EyeOutlined } from "@ant-design/icons";

import type { ISentence } from "../../api/sentence";

import type { IWbw } from "../../types/wbw";
import { sentSave } from "../../api/sentence";
import WbwSentCtl from "../wbw/WbwSentCtl";

interface IWidget {
  data: ISentence;
  onSave?: (newSent: ISentence) => void;
  onClose?: () => void;
}
const SentWbwEditWidget = ({ data, onSave, onClose }: IWidget) => {
  const intl = useIntl();

  const wbwData = useMemo(() => {
    if (data.contentType === "json" && data.content) {
      return JSON.parse(data.content);
    } else {
      return [];
    }
  }, [data.content, data.contentType]);

  return (
    <div style={{ width: "100%" }}>
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
        channelLang={data.channel.lang}
        onChange={(wbwData: IWbw[]) => {
          const newSent = { ...data };
          newSent.content = JSON.stringify(wbwData);
          sentSave(newSent)
            .then((value) => {
              if (value) {
                newSent.html = value.data.html;
                onSave?.(newSent);
              } else {
                console.error("返回数据失败");
              }
            })
            .catch((error) => {
              message.error(intl.formatMessage({ id: "errors.saveFailed" }));
              console.error(error);
            });
        }}
      />

      <div>
        <Button
          size="small"
          type="primary"
          icon={<EyeOutlined />}
          onClick={() => {
            onClose?.();
          }}
        >
          {intl.formatMessage({ id: "buttons.preview" })}
        </Button>
      </div>
    </div>
  );
};

export default SentWbwEditWidget;

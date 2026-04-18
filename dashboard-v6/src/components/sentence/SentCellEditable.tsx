import { useCallback, useState } from "react";
import { useIntl } from "react-intl";
import { Button, message, Typography } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { post, put } from "../../request";
import type { ISentence } from "../../api/sentence";

import TermTextArea from "../general/TermTextArea";
import { useAppSelector } from "../../hooks";
import { wordList } from "../../reducers/sent-word";

import { sentSave } from "../../api/sentence";
import TplBuilder from "../tpl-builder/TplBuilder";
import type {
  ISentencePrRequest,
  ISentencePrResponse,
} from "../../api/sentence-pr";
import { toISentence } from "./utils";

const { Text } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  isCreatePr?: boolean;
  onSave?: (data: ISentence) => void;
  onPrSave?: () => void;
  onClose?: () => void;
  onCreate?: () => void;
}
const SentCellEditable = ({
  data,
  onSave,
  onPrSave,
  onClose,
  onCreate,
  isPr = false,
  isCreatePr = false,
}: IWidget) => {
  const intl = useIntl();
  const [value, setValue] = useState(data.content);
  const [saving, setSaving] = useState<boolean>(false);
  const sentWords = useAppSelector(wordList);

  const sentId = `${data.book}-${data.para}-${data.wordStart}-${data.wordEnd}`;
  const termList = sentWords.find((value) => value.sentId === sentId)?.words;
  const save = () => {
    if (!value) {
      return;
    }
    setSaving(true);
    sentSave({ ...data, content: value })
      .then((json) => {
        if (json?.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onSave !== "undefined") {
            const newData: ISentence = toISentence(json.data);
            onSave(newData);
          }
        } else {
          message.error(json?.message);
        }
      })
      .finally(() => {
        setSaving(false);
      })
      .catch((e) => {
        console.error("catch", e);
        message.error(e.message);
      });
  };

  const createPr = useCallback(() => {
    if (!value) {
      return;
    }
    setSaving(true);
    const newData: ISentencePrRequest = {
      book: data.book,
      para: data.para,
      begin: data.wordStart,
      end: data.wordEnd,
      channel: data.channel.id,
      text: value ?? "",
    };
    post<ISentencePrRequest, ISentencePrResponse>(`/v2/sentpr`, newData)
      .then((json) => {
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
        console.error("catch", e);
        message.error(e.message);
      })
      .finally(() => {
        setSaving(false);
      });
  }, [data, intl, onCreate, value]);

  const updatePr = useCallback(() => {
    if (!value) {
      return;
    }
    setSaving(true);
    const url = `/v2/sentpr/${data.id}`;
    console.log("url", url);
    put<ISentencePrRequest, ISentencePrResponse>(url, {
      text: value,
    })
      .then((json) => {
        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onPrSave !== "undefined") {
            onPrSave();
          }
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setSaving(false);
      })
      .catch((e) => {
        console.error("catch", e);
        message.error(e.message);
      });
  }, [data.id, intl, onPrSave, value]);

  const savePr = () => {
    if (isCreatePr) {
      createPr();
    } else {
      updatePr();
    }
  };

  return (
    <Typography.Paragraph style={{ width: "100%" }}>
      <TermTextArea
        value={value ? value : ""}
        menuOptions={termList}
        onChange={(value: string) => {
          setValue(value);
        }}
        placeholder={intl.formatMessage({
          id: "labels.input",
        })}
        onClose={() => {
          if (typeof onClose !== "undefined") {
            onClose();
          }
        }}
        onSave={(value?: string) => {
          if (value) {
            setValue(value);
            if (isPr) {
              savePr();
            } else {
              save();
            }
          }
        }}
      />
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <div>
          <span>
            <Text keyboard>esc</Text>=
            <Button
              size="small"
              type="link"
              onClick={() => {
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
            <TplBuilder trigger={"<t>"} />
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

export default SentCellEditable;

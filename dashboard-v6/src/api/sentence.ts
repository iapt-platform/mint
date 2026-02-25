import type { IntlShape } from "react-intl";
import type {
  ISentence,
  ISentenceData,
  ISentenceRequest,
  ISentenceResponse,
} from "./Corpus";
import store from "../store";
import { statusChange } from "../reducers/net-status";
import { put } from "../request";
import { message } from "antd";
import { toISentence } from "../components/sentence/utils";

export const sentSave = async (
  sent: ISentence,
  intl: IntlShape,
  ok?: (res: ISentence) => void,
  finish?: () => void
): Promise<ISentenceData | null> => {
  store.dispatch(statusChange({ status: "loading" }));
  const id = `${sent.book}_${sent.para}_${sent.wordStart}_${sent.wordEnd}_${sent.channel.id}`;
  const url = `/v2/sentence/${id}?mode=edit&html=true`;
  console.info("SentWbwEdit url", url);

  try {
    const res = await put<ISentenceRequest, ISentenceResponse>(url, {
      book: sent.book,
      para: sent.para,
      wordStart: sent.wordStart,
      wordEnd: sent.wordEnd,
      channel: sent.channel.id,
      content: sent.content,
      contentType: sent.contentType,
      channels: sent.translationChannels?.join(),
      token: sessionStorage.getItem(sent.channel.id),
    });
    if (res.ok) {
      if (ok) {
        console.debug("sent save ok", res.data);
        const newData: ISentence = toISentence(res.data);
        ok(newData);
      }

      store.dispatch(
        statusChange({
          status: "success",
          message: intl.formatMessage({ id: "flashes.success" }),
        })
      );
      return res.data;
    } else {
      message.error(res.message);
      store.dispatch(
        statusChange({
          status: "fail",
          message: res.message,
        })
      );
      return null;
    }
  } catch (e) {
    console.error("catch", e);
    return null;
  } finally {
    finish?.();
  }
};

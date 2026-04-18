import type { ISentence, ISentenceData } from "../../api/sentence";

import type { ISentCart } from "./SentCart";
import store from "../../store";
import { show } from "../../reducers/discussion";
import { openPanel } from "../../reducers/right-panel";

export const addToCart = (add: ISentCart[]): number => {
  const oldText = localStorage.getItem("cart/text");
  let cartText: ISentCart[] = [];
  if (oldText) {
    cartText = JSON.parse(oldText);
  }
  cartText = [...cartText, ...add];
  localStorage.setItem("cart/text", JSON.stringify(cartText));
  return cartText.length;
};

export const prOpen = (data: ISentence) => {
  store.dispatch(
    show({
      type: "pr",
      sent: data,
    })
  );
  store.dispatch(openPanel("suggestion"));
};

export const toISentence = (
  item: ISentenceData,
  channelsId?: string[]
): ISentence => {
  return {
    id: item.id,
    content: item.content,
    contentType: item.content_type,
    html: item.html,
    book: item.book,
    para: item.paragraph,
    wordStart: item.word_start,
    wordEnd: item.word_end,
    editor: item.editor,
    studio: item.studio,
    channel: item.channel,
    updateAt: item.updated_at,
    acceptor: item.acceptor,
    prEditAt: item.pr_edit_at,
    forkAt: item.fork_at,
    suggestionCount: item.suggestionCount,
    translationChannels: channelsId,
  };
};

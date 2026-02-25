import type { ISentence } from "../../api/Corpus";

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

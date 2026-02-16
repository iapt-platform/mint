import { useEffect } from "react";
import { get } from "../../request";
import { ISuggestionResponse } from "../api/Suggestion";
import store from "../../store";
import { refresh } from "../../reducers/pr-load";

interface IWidget {
  uid?: string | null;
}
const PrPullWidget = ({ uid }: IWidget) => {
  useEffect(() => {
    if (!uid) {
      return;
    }
    const url = `/v2/sentpr/${uid}`;
    console.log("url", url);
    get<ISuggestionResponse>(url)
      .then((json) => {
        if (json.ok) {
          store.dispatch(refresh(json.data));
        }
      })
      .catch((e) => console.error(e));
  }, [uid]);
  return <></>;
};

export default PrPullWidget;

import { Button, message } from "antd";
import React from "react";
import { post } from "../../request";
import AnthologyModal from "../anthology/AnthologyModal";
import { IArticleMapAddRequest, IArticleMapAddResponse } from "../api/Article";
interface IWidget {
  trigger?: React.ReactNode;
  studioName?: string;
  articleIds?: string[];
  onFinally?: Function;
}
const Widget = ({ trigger, studioName, articleIds, onFinally }: IWidget) => {
  return (
    <AnthologyModal
      studioName={studioName}
      trigger={trigger ? trigger : <Button type="link">加入文集</Button>}
      onSelect={(id: string) => {
        if (typeof articleIds !== "undefined") {
          post<IArticleMapAddRequest, IArticleMapAddResponse>(
            "/v2/article-map",
            {
              anthology_id: id,
              article_id: articleIds,
              operation: "add",
            }
          )
            .finally(() => {
              if (typeof onFinally !== "undefined") {
                onFinally();
              }
            })
            .then((json) => {
              if (json.ok) {
                message.success(json.data);
              } else {
                message.error(json.message);
              }
            })
            .catch((e) => console.error(e));
        }
      }}
    />
  );
};

export default Widget;

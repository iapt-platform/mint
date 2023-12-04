import { Button, message } from "antd";
import React, { useEffect, useState } from "react";
import { post } from "../../request";
import AnthologyModal from "../anthology/AnthologyModal";
import { IArticleMapAddRequest, IArticleMapAddResponse } from "../api/Article";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
interface IWidget {
  trigger?: React.ReactNode;
  studioName?: string;
  articleIds?: string[];
  open?: boolean;
  onClose?: Function;
  onFinally?: Function;
}
const AddToAnthologyWidget = ({
  trigger,
  studioName,
  open = false,
  onClose,
  articleIds,
  onFinally,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const user = useAppSelector(currentUser);
  useEffect(() => setIsModalOpen(open), [open]);
  return (
    <AnthologyModal
      studioName={studioName ? studioName : user?.realName}
      trigger={trigger}
      open={isModalOpen}
      onClose={(isOpen: boolean) => setIsModalOpen(isOpen)}
      onSelect={(id: string) => {
        if (typeof articleIds !== "undefined") {
          const url = "/v2/article-map";
          console.log("url", url);
          post<IArticleMapAddRequest, IArticleMapAddResponse>(url, {
            anthology_id: id,
            article_id: articleIds,
            operation: "add",
          })
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

export default AddToAnthologyWidget;

import { message } from "antd";
import React, { useState } from "react";
import { post } from "../../request";
import AnthologyModal from "../anthology/AnthologyModal";
import type {
  IArticleMapAddRequest,
  IArticleMapAddResponse,
} from "../../api/article";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IWidget {
  trigger?: React.ReactNode;
  studioName?: string;
  articleIds?: string[];
  open?: boolean; // 外部控制
  onClose?: (open: boolean) => void;
  onFinally?: () => void;
}

const AddToAnthologyWidget = ({
  trigger,
  studioName,
  open,
  articleIds,
  onClose,
  onFinally,
}: IWidget) => {
  const user = useAppSelector(currentUser);

  /** 是否受控 */
  const isControlled = open !== undefined;

  /** 内部状态（仅非受控使用） */
  const [innerOpen, setInnerOpen] = useState(false);

  /** 最终状态来源 */
  const isOpen = isControlled ? open : innerOpen;

  /** 状态修改统一入口 */
  const setOpen = (next: boolean) => {
    if (!isControlled) {
      setInnerOpen(next);
    }
    onClose?.(next);
  };

  /** 选择文集 */
  const handleSelect = (id: string) => {
    if (!articleIds) return;

    post<IArticleMapAddRequest, IArticleMapAddResponse>("/v2/article-map", {
      anthology_id: id,
      article_id: articleIds,
      operation: "add",
    })
      .then((json) => {
        if (json.ok) {
          message.success(json.data);
          setOpen(false); // 成功后关闭
        } else {
          message.error(json.message);
        }
      })
      .catch(console.error)
      .finally(() => {
        onFinally?.();
      });
  };

  return (
    <AnthologyModal
      studioName={studioName ?? user?.realName}
      trigger={trigger && <span onClick={() => setOpen(true)}>{trigger}</span>}
      open={isOpen}
      onClose={setOpen}
      onSelect={handleSelect}
    />
  );
};

export default AddToAnthologyWidget;

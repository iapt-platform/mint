import { Button, Dropdown } from "antd";
import {
  ReloadOutlined,
  MoreOutlined,
  InboxOutlined,
  EditOutlined,
  FileOutlined,
  CopyOutlined,
} from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import AddToAnthology from "./AddToAnthology";
import { useState } from "react";
import { fullUrl } from "../../utils";
import { ArticleTplModal } from "../template/Builder/ArticleTpl";
import AnthologiesAtArticle from "./AnthologiesAtArticle";
import { TRole } from "../api/Auth";

interface IWidget {
  articleId?: string;
  anthologyId?: string | null;
  title?: string;
  role?: TRole;
  onEdit?: Function;
  onAnthologySelect?: Function;
}
const TypeArticleReaderToolbarWidget = ({
  articleId,
  anthologyId,
  title,
  role = "reader",
  onEdit,
  onAnthologySelect,
}: IWidget) => {
  const user = useAppSelector(currentUser);
  const [addToAnthologyOpen, setAddToAnthologyOpen] = useState(false);
  const [tplOpen, setTplOpen] = useState(false);

  const editable = role === "owner" || role === "manager" || role === "editor";

  return (
    <div>
      <div
        style={{ padding: 4, display: "flex", justifyContent: "space-between" }}
      >
        <div>
          <AnthologiesAtArticle
            articleId={articleId}
            anthologyId={anthologyId}
            onClick={(
              id: string,
              e: React.MouseEvent<HTMLElement, MouseEvent>
            ) => {
              if (typeof onAnthologySelect !== "undefined") {
                onAnthologySelect(id, e);
              }
            }}
          />
        </div>
        <div>
          <Button
            type="link"
            shape="round"
            size="small"
            icon={<ReloadOutlined />}
          />
          <Dropdown
            menu={{
              items: [
                {
                  label: "添加到文集",
                  key: "add_to_anthology",
                  icon: <InboxOutlined />,
                  disabled: user ? false : true,
                },
                {
                  label: "编辑",
                  key: "edit",
                  icon: <EditOutlined />,
                  disabled: !editable,
                },
                {
                  label: "在Studio中打开",
                  key: "open-studio",
                  icon: <EditOutlined />,
                  disabled: user ? false : true,
                },
                {
                  label: "获取文章引用模版",
                  key: "tpl",
                  icon: <FileOutlined />,
                },
                {
                  label: "创建副本",
                  key: "fork",
                  icon: <CopyOutlined />,
                  disabled: user ? false : true,
                },
              ],
              onClick: ({ key }) => {
                console.log(`Click on item ${key}`);
                switch (key) {
                  case "add_to_anthology":
                    setAddToAnthologyOpen(true);
                    break;
                  case "fork":
                    const url = `/studio/${user?.nickName}/article/create?parent=${articleId}`;
                    window.open(fullUrl(url), "_blank");
                    break;
                  case "tpl":
                    setTplOpen(true);
                    break;
                  case "edit":
                    if (typeof onEdit !== "undefined") {
                      onEdit();
                    }
                    break;
                }
              },
            }}
            placement="bottomRight"
          >
            <Button
              onClick={(e) => e.preventDefault()}
              icon={<MoreOutlined />}
              size="small"
              type="link"
            />
          </Dropdown>
        </div>
      </div>
      {articleId ? (
        <AddToAnthology
          open={addToAnthologyOpen}
          onClose={(isOpen: boolean) => setAddToAnthologyOpen(isOpen)}
          articleIds={[articleId]}
        />
      ) : undefined}

      <ArticleTplModal
        title={title}
        type="article"
        id={articleId}
        open={tplOpen}
        onOpenChange={(visible: boolean) => setTplOpen(visible)}
      />
    </div>
  );
};

export default TypeArticleReaderToolbarWidget;

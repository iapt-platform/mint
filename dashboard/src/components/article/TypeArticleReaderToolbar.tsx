import { Button, Dropdown, Tooltip } from "antd";
import {
  ReloadOutlined,
  MoreOutlined,
  InboxOutlined,
  EditOutlined,
  FileOutlined,
  CopyOutlined,
  InfoCircleOutlined,
} from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import AddToAnthology from "./AddToAnthology";
import { useState } from "react";
import { fullUrl } from "../../utils";
import { ArticleTplModal } from "../template/Builder/ArticleTpl";
import AnthologiesAtArticle from "./AnthologiesAtArticle";
import { TRole } from "../api/Auth";
import { useIntl } from "react-intl";
import { TabIcon } from "../../assets/icon";
import WordCount from "./WordCount";

interface IWidget {
  articleId?: string;
  anthologyId?: string | null;
  title?: string;
  role?: TRole;
  isSubWindow?: boolean;
  onEdit?: Function;
  onAnthologySelect?: Function;
}
const TypeArticleReaderToolbarWidget = ({
  articleId,
  anthologyId,
  title,
  role = "reader",
  isSubWindow = false,
  onEdit,
  onAnthologySelect,
}: IWidget) => {
  const intl = useIntl();
  const user = useAppSelector(currentUser);
  const [addToAnthologyOpen, setAddToAnthologyOpen] = useState(false);
  const [tplOpen, setTplOpen] = useState(false);
  const [wordCountOpen, setWordCountOpen] = useState(false);

  const editable = role === "owner" || role === "manager" || role === "editor";

  return (
    <div>
      <div
        style={{ padding: 4, display: "flex", justifyContent: "space-between" }}
      >
        <div>
          {isSubWindow ? (
            <></>
          ) : (
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
          )}
        </div>
        <div>
          <Tooltip
            title={intl.formatMessage({
              id: "buttons.edit",
            })}
          >
            <Button
              type="link"
              size="small"
              disabled={!editable}
              icon={<EditOutlined />}
              onClick={() => {
                if (typeof onEdit !== "undefined") {
                  onEdit();
                }
              }}
            />
          </Tooltip>
          <Button type="link" size="small" icon={<ReloadOutlined />} />
          <Dropdown
            menu={{
              items: [
                {
                  label: intl.formatMessage({
                    id: "buttons.open.in.new.tab",
                  }),
                  key: "open_in_tab",
                  icon: <TabIcon />,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.add_to_anthology",
                  }),
                  key: "add_to_anthology",
                  icon: <InboxOutlined />,
                  disabled: user ? false : true,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.edit",
                  }),
                  key: "edit",
                  icon: <EditOutlined />,
                  disabled: !editable,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.open.in.studio",
                  }),
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
                {
                  label: "字数统计",
                  key: "word-count",
                  icon: <InfoCircleOutlined />,
                },
              ],
              onClick: ({ key }) => {
                console.log(`Click on item ${key}`);
                switch (key) {
                  case "open_in_tab":
                    window.open(
                      fullUrl(`/article/article/${articleId}`),
                      "_blank"
                    );
                    break;
                  case "add_to_anthology":
                    setAddToAnthologyOpen(true);
                    break;
                  case "fork":
                    const url = `/studio/${user?.realName}/article/create?parent=${articleId}`;
                    window.open(fullUrl(url), "_blank");
                    break;
                  case "tpl":
                    setTplOpen(true);
                    break;
                  case "word-count":
                    setWordCountOpen(true);
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
      <WordCount
        open={wordCountOpen}
        articleId={articleId}
        onClose={() => setWordCountOpen(false)}
      />
    </div>
  );
};

export default TypeArticleReaderToolbarWidget;

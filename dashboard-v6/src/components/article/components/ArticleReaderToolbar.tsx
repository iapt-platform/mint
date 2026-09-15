import { Button, Dropdown, Space } from "antd";
import {
  ReloadOutlined,
  MoreOutlined,
  InboxOutlined,
  EditOutlined,
  FileOutlined,
  CopyOutlined,
  InfoCircleOutlined,
  ShareAltOutlined,
  ExportOutlined,
  EyeOutlined,
} from "@ant-design/icons";

import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

import { useState } from "react";
import { articlePath, fullUrl } from "../../../utils";

import type { TRole } from "../../../api/Auth";
import { useIntl } from "react-intl";
import { TabIcon } from "../../../assets/icon";

import AnthologiesAtArticle from "../../anthology/AnthologiesAtArticle";
import AddToAnthology from "../../anthology/AddToAnthology";
import TplBuilder from "../../tpl-builder/TplBuilder";
import WordCount from "../WordCount";

interface IWidget {
  articleId?: string;
  anthologyId?: string | null;
  title?: string;
  role?: TRole;
  isSubWindow?: boolean;
  onRefresh?: () => void;
  onEdit?: () => void;
  onAnthologySelect?: (
    id: string,
    e: React.MouseEvent<HTMLElement, MouseEvent>
  ) => void;
}
const TypeArticleReaderToolbarWidget = ({
  articleId,
  anthologyId,
  title,
  role = "reader",
  isSubWindow = false,
  onRefresh,
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
              onClick={(id, e) => {
                if (onAnthologySelect) {
                  onAnthologySelect(id, e);
                }
              }}
            />
          )}
        </div>
        <Space>
          {/** 编辑按钮 */}
          <Button
            type="primary"
            disabled={!editable}
            icon={<EditOutlined />}
            onClick={() => {
              if (typeof onEdit !== "undefined") {
                onEdit();
              }
            }}
          >
            {intl.formatMessage({
              id: "buttons.edit",
            })}
          </Button>
          {/** 导出 */}
          {/** 更多 */}
          <Dropdown
            menu={{
              items: [
                {
                  label: intl.formatMessage(
                    {
                      id: "buttons.open.in.new.tab",
                    },
                    { item: "" }
                  ),
                  key: "open_in_tab",
                  icon: <TabIcon />,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.export",
                  }),
                  key: "export",
                  icon: <ExportOutlined />,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.open.in.library",
                  }),
                  key: "open_in_library",
                  icon: <EyeOutlined />,
                },
              ],
              onClick: ({ key }) => {
                console.log(`Click on item ${key}`);
                switch (key) {
                  case "open_in_tab":
                    window.open(
                      fullUrl(articlePath("article", articleId ?? "")),
                      "_blank"
                    );
                    break;
                  case "open_in_library":
                    window.open(
                      import.meta.env.VITE_APP_API_SERVER +
                        `/library/anthology/${anthologyId}/read/${articleId}`,
                      "_blank"
                    );
                    break;
                  case "add_to_anthology":
                    setAddToAnthologyOpen(true);
                    break;
                }
              },
            }}
            placement="bottomRight"
          >
            <Button
              onClick={(e) => e.preventDefault()}
              icon={<ShareAltOutlined />}
              size="small"
              type="link"
            />
          </Dropdown>
          {/** 刷新按钮 */}
          <Button type="link" icon={<ReloadOutlined />} onClick={onRefresh} />

          {/** 更多 */}
          <Dropdown
            menu={{
              items: [
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
                    id: "buttons.get_template",
                  }),
                  key: "tpl",
                  icon: <FileOutlined />,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.duplicate",
                  }),
                  key: "fork",
                  icon: <CopyOutlined />,
                  disabled: user ? false : true,
                },
                {
                  label: intl.formatMessage({
                    id: "buttons.word_count",
                  }),
                  key: "word-count",
                  icon: <InfoCircleOutlined />,
                },
              ],
              onClick: ({ key }) => {
                console.log(`Click on item ${key}`);
                switch (key) {
                  case "add_to_anthology":
                    setAddToAnthologyOpen(true);
                    break;
                  case "fork": {
                    const url = `/studio/${user?.realName}/article/create?parent=${articleId}`;
                    window.open(fullUrl(url), "_blank");
                    break;
                  }
                  case "tpl":
                    setTplOpen(true);
                    break;
                  case "word-count":
                    setWordCountOpen(true);
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
        </Space>
      </div>
      {articleId ? (
        <AddToAnthology
          open={addToAnthologyOpen}
          onClose={(isOpen: boolean) => setAddToAnthologyOpen(isOpen)}
          articleIds={[articleId]}
        />
      ) : undefined}

      <TplBuilder
        title={title}
        tpl="article"
        articleId={articleId}
        open={tplOpen}
        onClose={() => setTplOpen(false)}
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

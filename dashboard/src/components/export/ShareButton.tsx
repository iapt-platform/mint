import { useState } from "react";
import { Button, Dropdown, Space, Typography } from "antd";
import {
  ShareAltOutlined,
  ExportOutlined,
  ForkOutlined,
  InboxOutlined,
} from "@ant-design/icons";

import ExportModal from "./ExportModal";
import { ArticleType } from "../article/Article";
import AddToAnthology from "../article/AddToAnthology";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { fullUrl } from "../../utils";

const { Text } = Typography;

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  book?: string | null;
  para?: string | null;
  channelId?: string | null;
  anthologyId?: string | null;
}
const ShareButtonWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  anthologyId,
}: IWidget) => {
  const [exportOpen, setExportOpen] = useState(false);
  const [addToAnthologyOpen, setAddToAnthologyOpen] = useState(false);
  const user = useAppSelector(currentUser);

  return (
    <>
      <Dropdown
        trigger={["click"]}
        menu={{
          items: [
            {
              label: (
                <Space>
                  {"Export"}
                  <Text type="secondary" style={{ fontSize: "80%" }}>
                    {"PDF,Word,Html"}
                  </Text>
                </Space>
              ),
              key: "export",
              icon: <ExportOutlined />,
            },
            {
              label: "添加到文集",
              key: "add_to_anthology",
              icon: <InboxOutlined />,
            },
            {
              label: "创建副本",
              key: "fork",
              icon: <ForkOutlined />,
              disabled: user && type === "article" ? false : true,
            },
          ],
          onClick: ({ key }) => {
            switch (key) {
              case "export":
                setExportOpen(true);
                break;
              case "add_to_anthology":
                setAddToAnthologyOpen(true);
                break;
              case "fork":
                const url = `/studio/${user?.nickName}/article/create?parent=${articleId}`;
                window.open(fullUrl(url), "_blank");
                break;
              default:
                break;
            }
          },
        }}
      >
        <Button type="text" icon={<ShareAltOutlined color="#fff" />} />
      </Dropdown>
      <ExportModal
        type={type}
        articleId={articleId}
        book={book}
        para={para}
        channelId={channelId}
        anthologyId={anthologyId}
        open={exportOpen}
        onClose={() => setExportOpen(false)}
      />
      {articleId ? (
        <AddToAnthology
          open={addToAnthologyOpen}
          onClose={(isOpen: boolean) => setAddToAnthologyOpen(isOpen)}
          articleIds={[articleId]}
        />
      ) : undefined}
    </>
  );
};

export default ShareButtonWidget;

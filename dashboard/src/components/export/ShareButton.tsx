import { Button, Dropdown, Space, Typography } from "antd";
import { ShareAltOutlined, ExportOutlined } from "@ant-design/icons";
import ExportModal from "./ExportModal";
import { useState } from "react";
import { ArticleType } from "../article/Article";
import AddToAnthology from "../article/AddToAnthology";

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
              icon: <ExportOutlined />,
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

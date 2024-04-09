import { useState } from "react";
import { Button, Dropdown, Space, Typography } from "antd";
import { ShareAltOutlined, ExportOutlined } from "@ant-design/icons";

import ExportModal from "./ExportModal";
import { ArticleType } from "../article/Article";

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
          ],
          onClick: ({ key }) => {
            switch (key) {
              case "export":
                setExportOpen(true);
                break;
              default:
                break;
            }
          },
        }}
      >
        <Button
          type="text"
          icon={
            <ShareAltOutlined style={{ color: "white", cursor: "pointer" }} />
          }
        />
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
    </>
  );
};

export default ShareButtonWidget;

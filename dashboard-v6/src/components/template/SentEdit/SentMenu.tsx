import { useIntl } from "react-intl";
import { Badge, Button, Dropdown, Space } from "antd";
import { MoreOutlined, CheckOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import RelatedPara from "../../corpus/RelatedPara";
import { ArticleMode } from "../../article/Article";

interface IWidget {
  book?: number;
  para?: number;
  loading?: boolean;
  mode?: ArticleMode;
  onMagicDict?: Function;
  onMenuClick?: Function;
}
const SentMenuWidget = ({
  book,
  para,
  mode,
  loading = false,
  onMagicDict,
  onMenuClick,
}: IWidget) => {
  const intl = useIntl();
  const items: MenuProps["items"] = [
    {
      key: "show-commentary",
      label: <RelatedPara book={book} para={para} />,
    },
    {
      key: "show-nissaya",
      label: "Nissaya",
    },
    {
      key: "copy-id",
      label: intl.formatMessage({ id: "buttons.copy.id" }),
    },
    {
      key: "copy-link",
      label: intl.formatMessage({ id: "buttons.copy.link" }),
    },
    {
      key: "affix",
      label: "总在最顶端开/关",
    },
    {
      type: "divider",
    },
    {
      key: "origin",
      label: "原文模式",
      children: [
        {
          key: "origin-auto",
          label: "自动",
          icon: (
            <CheckOutlined
              style={{ visibility: mode === undefined ? "visible" : "hidden" }}
            />
          ),
        },
        {
          key: "origin-edit",
          label: "翻译",
          icon: (
            <CheckOutlined
              style={{ visibility: mode === "edit" ? "visible" : "hidden" }}
            />
          ),
        },
        {
          key: "origin-wbw",
          label: "逐词",
          icon: (
            <CheckOutlined
              style={{ visibility: mode === "wbw" ? "visible" : "hidden" }}
            />
          ),
        },
      ],
    },
    {
      key: "compact",
      label: (
        <Space>
          {intl.formatMessage({ id: "buttons.compact" })}
          <Badge count="Beta" showZero color="#faad14" />
        </Space>
      ),
    },
    {
      key: "normal",
      label: "正常",
    },
  ];
  const onClick: MenuProps["onClick"] = ({ key }) => {
    console.log(`Click on item ${key}`);
    if (typeof onMenuClick !== "undefined") {
      onMenuClick(key);
    }
    switch (key) {
      case "magic-dict-current":
        if (typeof onMagicDict !== "undefined") {
          onMagicDict("current");
        }
        break;
      default:
        break;
    }
  };
  return (
    <Dropdown menu={{ items, onClick }} placement="topRight">
      <Button
        loading={loading}
        onClick={(e) => e.preventDefault()}
        icon={<MoreOutlined />}
        size="small"
        type="primary"
      />
    </Dropdown>
  );
};

export default SentMenuWidget;

import { Button, Dropdown, Typography, Tag } from "antd";
import {
  DownOutlined,
  ReloadOutlined,
  GlobalOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";

const { Text } = Typography;

const ModelSelector = () => {
  const modelItems: MenuProps["items"] = [
    {
      key: "auto",
      label: (
        <div className="py-2">
          <div className="font-medium text-gray-900">Auto</div>
        </div>
      ),
    },
    {
      key: "gpt-4o",
      label: (
        <div className="py-2">
          <div className="font-medium text-gray-900">
            GPT-4o<Tag>翻译</Tag>
          </div>
          <Text type="secondary">适用于大多数任务</Text>
        </div>
      ),
    },
    {
      key: "o4-mini",
      label: (
        <div className="py-2">
          <div className="font-medium text-gray-900">o4-mini</div>
          <Text type="secondary">快速进行高级推理</Text>
        </div>
      ),
    },
    {
      key: "gpt-4.1-mini",
      label: (
        <div className="py-2">
          <div className="font-medium text-gray-900">GPT-4.1-mini</div>
          <Text type="secondary">适合处理日常任务</Text>
        </div>
      ),
    },
    {
      type: "divider",
    },
    {
      key: "refresh",
      label: (
        <div className="py-2 flex items-center space-x-2 text-gray-700">
          <ReloadOutlined />
          <span>重试</span>
          <div className="ml-auto">
            <Text type="secondary">GPT-4o</Text>
          </div>
        </div>
      ),
    },
    {
      key: "search",
      label: (
        <div className="py-2 flex items-center space-x-2 text-gray-700">
          <GlobalOutlined />
          <span>搜索网页</span>
        </div>
      ),
    },
  ];

  const handleMenuClick: MenuProps["onClick"] = ({ key }) => {
    if (key === "refresh") {
      console.log("重试操作");
      return;
    }
    if (key === "search") {
      console.log("搜索网页");
      return;
    }
  };

  return (
    <div className="bg-gray-50 min-h-screen">
      <div className="max-w-md mx-auto">
        <Dropdown
          menu={{
            items: modelItems,
            onClick: handleMenuClick,
            className: "w-64",
          }}
          trigger={["click"]}
          placement="bottomLeft"
          overlayClassName="model-selector-dropdown"
        >
          <Button
            className="flex items-center justify-between w-48 h-12 px-4 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 shadow-sm"
            type="text"
          >
            <div className="flex items-center space-x-2">
              <span className="font-medium text-gray-900 model_name">
                {"AI"}
              </span>
              <DownOutlined className="text-gray-500 text-sm" />
            </div>
          </Button>
        </Dropdown>
      </div>

      <style>{`
        .model-selector-dropdown .ant-dropdown-menu {
          padding: 8px;
          border-radius: 12px;
          box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
          border: 1px solid #e5e7eb;
        }

        .model-selector-dropdown .ant-dropdown-menu-item {
          padding: 0;
          margin: 2px 0;
          border-radius: 8px;
        }

        .model-selector-dropdown .ant-dropdown-menu-item:hover {
          background-color: #f3f4f6;
        }

        .model-selector-dropdown .ant-dropdown-menu-item-selected {
          background-color: #eff6ff;
        }

        .model-selector-dropdown .ant-dropdown-menu-divider {
          margin: 8px 0;
          background-color: #e5e7eb;
        }
      `}</style>
    </div>
  );
};

export default ModelSelector;

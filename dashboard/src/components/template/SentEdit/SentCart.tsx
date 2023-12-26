import { Badge, Button, List, Popover, Tooltip, Typography } from "antd";
import { useEffect, useState } from "react";
import { ShoppingCartOutlined, DeleteOutlined } from "@ant-design/icons";

const { Text } = Typography;

const SentCartWidget = () => {
  const [count, setCount] = useState<number>();
  const [sentences, setSentences] = useState<string[]>();

  const query = () => {
    const cartText = localStorage.getItem("cart/text");
    if (cartText) {
      const sent: string[] = JSON.parse(cartText);
      setCount(sent.length);
      setSentences(sent);
    } else {
      setCount(0);
    }
  };

  useEffect(() => {
    let timer = setInterval(query, 1000 * 2);
    return () => {
      clearInterval(timer);
    };
  }, []);

  return (
    <>
      <Popover
        placement="bottomRight"
        arrowPointAtCenter
        destroyTooltipOnHide
        getTooltipContainer={(node: HTMLElement) =>
          document.getElementsByClassName("toolbar_center")[0] as HTMLElement
        }
        content={
          <div>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <div></div>
              <div>
                <Text copyable={{ text: sentences?.join("\n") }} />
                <Tooltip title="清空列表保留剪贴板数据">
                  <Button
                    type="link"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => {
                      localStorage.removeItem("cart/text");
                      setSentences(undefined);
                      setCount(0);
                    }}
                  />
                </Tooltip>
              </div>
            </div>
            <div style={{ width: 350, height: 300, overflowY: "auto" }}>
              <List
                size="small"
                dataSource={sentences}
                renderItem={(item) => <List.Item>{item}</List.Item>}
              />
            </div>
          </div>
        }
        trigger="click"
      >
        <Badge style={{ cursor: "pointer" }} count={count} size="small">
          <span style={{ color: "white", cursor: "pointer" }}>
            <ShoppingCartOutlined />
          </span>
        </Badge>
      </Popover>
    </>
  );
};

export default SentCartWidget;

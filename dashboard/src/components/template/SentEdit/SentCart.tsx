import { Badge, Button, List, Popover, Tooltip, Typography } from "antd";
import { useEffect, useState } from "react";
import { ShoppingCartOutlined, DeleteOutlined } from "@ant-design/icons";

import "./style.css";

const { Text } = Typography;

export interface ISentCart {
  id: string;
  text: string;
}

export const addToCart = (add: ISentCart[]): number => {
  const oldText = localStorage.getItem("cart/text");
  let cartText: ISentCart[] = [];
  if (oldText) {
    cartText = JSON.parse(oldText);
  }
  cartText = [...cartText, ...add];
  localStorage.setItem("cart/text", JSON.stringify(cartText));
  return cartText.length;
};

const SentCartWidget = () => {
  const [count, setCount] = useState<number>();
  const [sentences, setSentences] = useState<ISentCart[]>();

  const query = () => {
    const cartText = localStorage.getItem("cart/text");
    if (cartText) {
      const sent: ISentCart[] = JSON.parse(cartText);
      setSentences(sent);
    } else {
      setSentences([]);
    }
  };

  useEffect(() => {
    if (sentences) {
      setCount(sentences.length);
      localStorage.setItem("cart/text", JSON.stringify(sentences));
    }
  }, [sentences]);

  useEffect(() => {
    query();
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
              <div>{"复制句子编号"}</div>
              <div>
                <Text
                  disabled={!sentences || sentences.length === 0}
                  copyable={{
                    text: sentences?.map((item) => item.id).join("\n"),
                  }}
                />
                <Tooltip title="清空列表保留剪贴板数据">
                  <Button
                    disabled={!sentences || sentences.length === 0}
                    type="link"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => {
                      setSentences([]);
                    }}
                  />
                </Tooltip>
              </div>
            </div>
            <div style={{ width: 450, height: 300, overflowY: "auto" }}>
              <List
                size="small"
                dataSource={sentences}
                renderItem={(item, index) => (
                  <List.Item key={index} className="cart_item">
                    <List.Item.Meta title={item.id} description={item.text} />
                    <Button
                      className="cart_delete"
                      type="link"
                      danger
                      icon={<DeleteOutlined />}
                      onClick={() => {
                        if (sentences) {
                          let newArr = [...sentences];
                          newArr.splice(index, 1);
                          console.debug("delete", index, newArr);
                          setSentences(newArr);
                        }
                      }}
                    />
                  </List.Item>
                )}
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

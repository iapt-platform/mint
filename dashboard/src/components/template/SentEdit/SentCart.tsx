import { Badge, Button, List, Popover, Tooltip, Typography } from "antd";
import { useEffect, useState } from "react";
import { ShoppingCartOutlined, DeleteOutlined } from "@ant-design/icons";
import { ISentCart } from "./SentTabCopy";
import "./style.css";

const { Text } = Typography;

const SentCartWidget = () => {
  const [count, setCount] = useState<number>();
  const [sentences, setSentences] = useState<ISentCart[]>();

  const query = () => {
    const cartText = localStorage.getItem("cart/text");
    if (cartText) {
      const sent: ISentCart[] = JSON.parse(cartText);
      setSentences(sent);
    } else {
      setSentences(undefined);
    }
  };

  useEffect(() => {
    if (sentences) {
      setCount(sentences.length);
      localStorage.setItem("cart/text", JSON.stringify(sentences));
    } else {
      setCount(0);
      localStorage.removeItem("cart/text");
    }
  }, [sentences]);

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
                      setSentences(undefined);
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
                    <List.Item.Meta title={item.text} description={item.id} />
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

import { Badge, Button, List, Popover, Tooltip, Typography } from "antd";
import { useEffect, useRef, useState } from "react";
import { ShoppingCartOutlined, DeleteOutlined } from "@ant-design/icons";

import "./style.css";

const { Text } = Typography;

export interface ISentCart {
  id: string;
  text: string;
}

const SentCartWidget = () => {
  const [sentences, setSentences] = useState<ISentCart[]>([]);
  const sentencesRef = useRef(sentences);

  // Keep ref in sync so the interval callback always sees latest value
  useEffect(() => {
    sentencesRef.current = sentences;
  }, [sentences]);

  // Derive count directly — no need for a separate state
  const count = sentences.length || undefined;

  // Sync sentences → localStorage whenever it changes
  useEffect(() => {
    localStorage.setItem("cart/text", JSON.stringify(sentences));
  }, [sentences]);

  // Poll localStorage for external changes (e.g. another tab / component)
  useEffect(() => {
    const syncFromStorage = () => {
      const raw = localStorage.getItem("cart/text");
      const next: ISentCart[] = raw ? JSON.parse(raw) : [];
      // Only call setState when the data actually changed to avoid extra renders
      if (JSON.stringify(next) !== JSON.stringify(sentencesRef.current)) {
        setSentences(next);
      }
    };

    syncFromStorage(); // initial load
    const timer = setInterval(syncFromStorage, 2000);
    return () => clearInterval(timer);
  }, []); // empty deps — runs once, uses ref for comparison

  return (
    <>
      <Popover
        placement="bottomRight"
        arrow={{ pointAtCenter: true }}
        destroyOnHidden
        getTooltipContainer={() =>
          document.getElementsByClassName("toolbar_center")[0] as HTMLElement
        }
        content={
          <div>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <div>{"复制句子编号"}</div>
              <div>
                <Text
                  disabled={sentences.length === 0}
                  copyable={{
                    text: sentences.map((item) => item.id).join("\n"),
                  }}
                />
                <Tooltip title="清空列表保留剪贴板数据">
                  <Button
                    disabled={sentences.length === 0}
                    type="link"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => setSentences([])}
                  />
                </Tooltip>
              </div>
            </div>
            <div style={{ width: 450, height: 300, overflowY: "auto" }}>
              <List
                size="small"
                dataSource={sentences}
                renderItem={(item, index) => (
                  <List.Item key={item.id} className="cart_item">
                    <List.Item.Meta title={item.id} description={item.text} />
                    <Button
                      className="cart_delete"
                      type="link"
                      danger
                      icon={<DeleteOutlined />}
                      onClick={() => {
                        setSentences((prev) =>
                          prev.filter((_, i) => i !== index)
                        );
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

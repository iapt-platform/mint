import { useState, useRef, useEffect } from "react";
import { Tabs, Button } from "antd";
import { CloseOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { siteInfo as _siteInfo } from "../../reducers/open-article";
import Article from "./Article";

const defaultPanes = [{ label: `Tab`, children: <></>, key: "1" }];

interface IWidget {
  onClose?: Function;
}
const Widget = ({ onClose }: IWidget) => {
  const [activeKey, setActiveKey] = useState("1");
  const [items, setItems] = useState(defaultPanes);
  const newTabIndex = useRef(0);

  const newArticle = useAppSelector(_siteInfo);

  useEffect(() => {
    console.log("open", newArticle);
    if (typeof newArticle !== "undefined") {
      add(newArticle?.title, newArticle?.url, newArticle?.id);
    }
  }, [newArticle]);

  const onChange = (key: string) => {
    setActiveKey(key);
  };

  const add = (title: string, url: string, id: string) => {
    const newActiveKey = `newTab${newTabIndex.current++}`;
    setItems([
      ...items,
      {
        label: title,
        children: (
          <Article active={true} type={url} articleId={id} mode="edit" />
        ),
        key: newActiveKey,
      },
    ]);
    setActiveKey(newActiveKey);
  };

  const remove = (
    targetKey:
      | React.MouseEvent<Element, MouseEvent>
      | React.KeyboardEvent<Element>
      | string
  ) => {
    const targetIndex = items.findIndex((pane) => pane.key === targetKey);
    const newPanes = items.filter((pane) => pane.key !== targetKey);
    if (newPanes.length && targetKey === activeKey) {
      const { key } =
        newPanes[
          targetIndex === newPanes.length ? targetIndex - 1 : targetIndex
        ];
      setActiveKey(key);
    }
    setItems(newPanes);
  };

  const onEdit = (
    targetKey:
      | React.MouseEvent<Element, MouseEvent>
      | React.KeyboardEvent<Element>
      | string,
    action: "add" | "remove"
  ) => {
    if (action === "add") {
      add("new", "url", "id");
    } else {
      remove(targetKey);
    }
  };
  const operations = (
    <Button
      icon={<CloseOutlined />}
      shape="circle"
      onClick={() => {
        if (onClose) {
          onClose(true);
        }
      }}
    />
  );
  return (
    <Tabs
      hideAdd
      onChange={onChange}
      activeKey={activeKey}
      type="editable-card"
      onEdit={onEdit}
      items={items}
      tabBarExtraContent={operations}
    />
  );
};

export default Widget;

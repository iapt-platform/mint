import { useIntl } from "react-intl";
import { Button, Dropdown, Input, Space } from "antd";
import { useMemo, useState } from "react";
import {
  MoreOutlined,
  EditOutlined,
  CheckOutlined,
  SearchOutlined,
  CloseOutlined,
} from "@ant-design/icons";
import { useAppSelector } from "../../hooks";

import { inlineDict as _inlineDict } from "../../reducers/inline-dict";
import store from "../../store";
import { lookup } from "../../reducers/command";
import { openPanel } from "../../reducers/right-panel";
import type { ItemType } from "antd/es/menu/interface";

interface IWidgetFactor {
  pali: string;
  meaning?: string;
  readonly?: boolean;
  onChange?: (input: string | undefined) => void;
}

const WbwFactorMeaningItem = ({
  pali,
  readonly = false,
  meaning = "",
  onChange,
}: IWidgetFactor) => {
  const intl = useIntl();
  const [input, setInput] = useState<string>();
  const [editable, setEditable] = useState(false);
  const inlineDict = useAppSelector(_inlineDict);

  // 1. Memoize the menu items to avoid useEffect/setState and dependency issues
  const menuItems = useMemo(() => {
    const defaultMenu: ItemType[] = [
      {
        key: "_lookup",
        label: (
          <Space>
            <SearchOutlined />
            {intl.formatMessage({ id: "buttons.lookup" })}
          </Space>
        ),
      },
      {
        key: "_edit",
        label: (
          <Space>
            <EditOutlined />
            {intl.formatMessage({ id: "buttons.edit" })}
          </Space>
        ),
      },
      { key: pali, label: pali },
      { type: "divider" }, // Visual separation
    ];

    if (!inlineDict.wordIndex.includes(pali)) {
      return defaultMenu;
    }

    const result = inlineDict.wordList.filter((word) => word.word === pali);

    // De-duplicate meanings
    const uniqueMeanings = new Set<string>();
    result.forEach((it) => {
      if (typeof it.mean === "string") {
        it.mean.split("$").forEach((m) => {
          if (m.trim() !== "") uniqueMeanings.add(m);
        });
      }
    });

    const dynamicMenu: ItemType[] = Array.from(uniqueMeanings).map((m) => ({
      key: m,
      label: m,
    }));

    const allItems = [...defaultMenu, ...dynamicMenu];

    // Handle "More" grouping logic within memo
    if (allItems.length <= 6) return allItems;

    return [
      ...allItems.slice(0, 5),
      {
        key: "more",
        label: intl.formatMessage({ id: "buttons.more" }),
        children: allItems.slice(5),
      },
    ];
  }, [pali, inlineDict, intl]);

  const handleOk = () => {
    setEditable(false);
    onChange?.(input);
  };

  const handleCancel = () => {
    setEditable(false);
    setInput(meaning);
  };

  const meaningInner = editable ? (
    <Input
      defaultValue={meaning}
      size="small"
      autoFocus // Better UX when switching to edit mode
      addonAfter={
        <Space size={4}>
          <CheckOutlined style={{ cursor: "pointer" }} onClick={handleOk} />
          <CloseOutlined style={{ cursor: "pointer" }} onClick={handleCancel} />
        </Space>
      }
      style={{ width: 160 }}
      onChange={(e) => setInput(e.target.value)}
      onPressEnter={handleOk}
      onKeyDown={(e) => e.key === "Escape" && handleCancel()}
    />
  ) : (
    <Button
      disabled={readonly}
      size="small"
      type="text"
      icon={meaning === "" ? <MoreOutlined /> : undefined}
      onClick={() => setEditable(true)}
    >
      {meaning}
    </Button>
  );

  if (editable || readonly) return meaningInner;

  return (
    <Dropdown
      menu={{
        items: menuItems,
        onClick: (e) => {
          if (e.key === "_lookup") {
            store.dispatch(lookup(pali));
            store.dispatch(openPanel("dict"));
          } else if (e.key === "_edit") {
            setEditable(true);
          } else {
            onChange?.(e.key);
          }
        },
      }}
      placement="bottomLeft"
      trigger={["hover"]}
    >
      {meaningInner}
    </Dropdown>
  );
};

export default WbwFactorMeaningItem;

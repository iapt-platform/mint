import { useEffect, useMemo, useState, useCallback } from "react";
import { Button, Dropdown, type MenuProps, Typography } from "antd";
import { LoadingOutlined, CloseOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";

import { type IWidgetSentEditInner, SentEditInner } from "./SentEdit";

import store from "../../store";
import { push } from "../../reducers/sentence";
import "./style.css";

import type { ISentence } from "../../api/sentence";
import { get } from "../../request";
import { GetUserSetting } from "../setting/default";
import type { TCodeConvertor } from "../../types/template";
import { openDiscussion } from "../discussion/utils";
import { prOpen } from "./utils";
import MdView from "../general/MdView";
import InteractiveButton from "./InteractiveButton";
import type { IEditableSentence } from "../../api/sentence";

const { Text } = Typography;

const items: MenuProps["items"] = [
  { label: "编辑", key: "edit" },
  { label: "讨论", key: "discussion" },
  { label: "修改建议", key: "pr" },
  { label: "标签", key: "tag" },
];

export interface IWidgetSentReadFrame {
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: "row" | "column";
  book?: number;
  para?: number;
  wordStart?: number;
  wordEnd?: number;
  sentId?: string;
  error?: string;
}

const SentReadFrame = ({
  origin,
  translation,
  book,
  para,
  wordStart,
  wordEnd,
  error,
}: IWidgetSentReadFrame) => {
  const settings = useAppSelector(settingInfo);

  const [loadingId, setLoadingId] = useState<string | null>(null);
  const [active, setActive] = useState(false);
  const [sentData, setSentData] = useState<IWidgetSentEditInner>();
  const [showEdit, setShowEdit] = useState(false);

  /** 派生数据：主巴利编码 */
  const paliCode = useMemo(() => {
    const v = GetUserSetting("setting.pali.script.primary", settings);
    return (v ?? "roman") as TCodeConvertor;
  }, [settings]);

  /** 派生数据：是否显示原文 */
  const displayOriginal = useMemo(() => {
    return GetUserSetting("setting.display.original", settings);
  }, [settings]);

  /** 派生数据：布局方向 */
  const layoutDirection = useMemo<React.CSSProperties["flexDirection"]>(() => {
    const v = GetUserSetting("setting.layout.direction", settings);
    if (
      v === "row" ||
      v === "column" ||
      v === "row-reverse" ||
      v === "column-reverse"
    ) {
      return v;
    }
    return "row";
  }, [settings]);

  /** push 到 store（副作用） */
  useEffect(() => {
    store.dispatch(
      push({
        id: `${book}-${para}-${wordStart}-${wordEnd}`,
        origin: origin?.map((item) => item.html),
        translation: translation?.map((item) => item.html),
      })
    );
  }, [book, origin, para, translation, wordEnd, wordStart]);

  /** 菜单点击 */
  const handleMenuClick = useCallback(async (key: string, item: ISentence) => {
    switch (key) {
      case "edit":
        if (!item.id) return;
        setLoadingId(item.id);

        try {
          const json = await get<IEditableSentence>(
            `/v2/editable-sentence/${item.id}`
          );
          if (json.ok) {
            setSentData(json.data);
            setShowEdit(true);
          }
        } finally {
          setLoadingId(null);
        }
        break;

      case "discussion":
        if (item.id) {
          openDiscussion(item.id, "sentence", false);
        }
        break;

      case "pr":
        prOpen(item);
        break;
    }
  }, []);

  return (
    <span
      className="sent_read_shell"
      style={{ flexDirection: layoutDirection }}
    >
      <Text type="danger" mark>
        {error}
      </Text>

      {/* anchor */}
      <span
        dangerouslySetInnerHTML={{
          __html: `<span class="pcd_sent" id="sent_${book}-${para}-${wordStart}-${wordEnd}"></span>`,
        }}
      />

      {/* 原文 */}
      <span
        style={{
          flex: 5,
          color: "#9f3a01",
          display:
            displayOriginal === false && translation?.length ? "none" : "block",
        }}
      >
        {origin?.map((item, id) => (
          <Text key={id}>
            <MdView
              style={{ color: "brown" }}
              html={item.html}
              wordWidget
              convertor={paliCode}
            />
          </Text>
        ))}
      </span>

      {/* 译文 */}
      <span className="sent_read" style={{ flex: 5 }}>
        {translation?.map((item, id) => (
          <span key={id}>
            {loadingId === item.id && <LoadingOutlined />}

            <Dropdown
              trigger={["contextMenu"]}
              menu={{
                items,
                onClick: (e) => handleMenuClick(e.key, item),
              }}
            >
              <Text
                className="sent_read_translation"
                style={{ display: showEdit ? "none" : "inline" }}
              >
                <MdView
                  html={item.html}
                  style={{ backgroundColor: active ? "beige" : undefined }}
                />
              </Text>
            </Dropdown>

            {/* 编辑面板 */}
            {showEdit && (
              <div>
                <div style={{ textAlign: "right" }}>
                  <Button
                    size="small"
                    icon={<CloseOutlined />}
                    onClick={() => setShowEdit(false)}
                  >
                    返回审阅模式
                  </Button>
                </div>

                {sentData ? (
                  <SentEditInner
                    mode="edit"
                    {...sentData}
                    onTranslationChange={(data: ISentence) => {
                      if (!translation) return;
                      const copy = [...translation];
                      copy[id] = data;
                    }}
                  />
                ) : (
                  "无数据"
                )}
              </div>
            )}

            <InteractiveButton
              data={item}
              compact
              float
              hideCount
              hideInZero
              onMouseEnter={() => setActive(true)}
              onMouseLeave={() => setActive(false)}
            />
          </span>
        ))}
      </span>
    </span>
  );
};

export default SentReadFrame;

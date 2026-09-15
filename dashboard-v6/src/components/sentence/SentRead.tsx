import { useEffect, useState, useCallback } from "react";
import { Dropdown, Flex, type MenuProps, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { type IWidgetSentEditInner } from "./SentEdit";

import store from "../../store";
import { push } from "../../reducers/sentence";
import "./style.css";

import type { ISentence } from "../../api/sentence";
import { get } from "../../request";
import { openDiscussion } from "../discussion/utils";
import { prOpen } from "./utils";
import InteractiveButton from "./InteractiveButton";
import type { IEditableSentence } from "../../api/sentence";
import MdOrigin from "./components/MdOrigin";
import EditPad from "./components/EditPad";
import MdTranslation from "./components/MdTranslation";
import CommentaryPad from "../tipitaka/components/CommentaryPad";
import { useSetting } from "../../hooks/useSetting";

const { Text } = Typography;

const items: MenuProps["items"] = [
  { label: "编辑", key: "edit" },
  { label: "讨论", key: "discussion" },
  { label: "修改建议", key: "pr" },
  { label: "标签", key: "tag" },
];

export interface IWidgetSentReadFrame {
  id?: string;
  book?: number;
  para?: number;
  wordStart?: number;
  wordEnd?: number;
  origin?: ISentence[];
  translation?: ISentence[];
  commentaries?: ISentence[];
  layout?: "row" | "column";
  show?: "origin" | "translation" | "both";
  error?: string;
}

const SentReadFrame = ({
  origin,
  translation,
  commentaries,
  book,
  para,
  wordStart,
  wordEnd,
  show = "both",
  error,
}: IWidgetSentReadFrame) => {
  const layoutDirection = useSetting("setting.layout.direction");
  const layoutCommentary = useSetting("setting.layout.commentary");
  const displayOriginal = useSetting("setting.display.original");

  const [loadingId, setLoadingId] = useState<string | null>(null);
  const [active, setActive] = useState(false);
  const [sentData, setSentData] = useState<IWidgetSentEditInner>();
  const [showEdit, setShowEdit] = useState(false);

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
            `/api/v2/editable-sentence/${item.id}`
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
    <div className="sent_read_shell">
      {error && (
        <Text type="danger" mark>
          {error}
        </Text>
      )}
      {/* anchor */}
      <div
        dangerouslySetInnerHTML={{
          __html: `<span class="pcd_sent" id="sent_${book}-${para}-${wordStart}-${wordEnd}"></span>`,
        }}
      />
      <Flex vertical={layoutCommentary === "column"}>
        <Flex
          gap="middle"
          vertical={layoutDirection === "column"}
          style={{ flex: 5 }}
        >
          {/* 原文 */}
          {(show === "both" || show === "origin") && (
            <span
              style={{
                flex: 5,
                color: "#9f3a01",
                display:
                  displayOriginal === false && translation?.length
                    ? "none"
                    : "block",
              }}
            >
              {origin?.map((item, id) => (
                <MdOrigin text={item.html} key={id} />
              ))}
            </span>
          )}

          {/* 译文 */}
          <span className="sent_read" style={{ flex: 5 }}>
            {translation?.map((item, id) => (
              <span key={id} style={{ border: active ? "1px" : "unset" }}>
                {loadingId === item.id && <LoadingOutlined />}

                <Dropdown
                  trigger={["contextMenu"]}
                  menu={{
                    items,
                    onClick: (e) => handleMenuClick(e.key, item),
                  }}
                >
                  {!showEdit && <MdTranslation text={item.html} />}
                </Dropdown>

                {/* 编辑面板 */}
                {showEdit && (
                  <EditPad
                    data={sentData}
                    onTranslationChange={(data: ISentence) => {
                      if (!translation) return;
                      const copy = [...translation];
                      copy[id] = data;
                    }}
                    onClose={() => setShowEdit(false)}
                  />
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
        </Flex>
        {/**注疏区 */}
        <CommentaryPad>
          {commentaries?.map((item, id) => {
            return <MdTranslation text={item.html} key={id} />;
          })}
        </CommentaryPad>
      </Flex>
    </div>
  );
};

export default SentReadFrame;

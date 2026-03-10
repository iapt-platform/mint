import { useEffect, useRef, useState, useMemo, useCallback } from "react";
import { Popover, Typography } from "antd";
import {
  TagTwoTone,
  InfoCircleOutlined,
  ApartmentOutlined,
  EditOutlined,
  QuestionCircleOutlined,
} from "@ant-design/icons";

import "./wbw.css";
import WbwDetail from "./WbwDetail";

import WbwVideoButton from "./WbwVideoButton";
import store from "../../store";
import { grammarId, lookup } from "../../reducers/command";
import { useAppSelector } from "../../hooks";
import { add, relationAddParam } from "../../reducers/relation-add";

import { anchor, showWbw } from "../../reducers/wbw";
//import { ParaLinkCtl } from "./ParaLink";

import WbwPaliDiscussionIcon from "./WbwPaliDiscussionIcon";
import type { TooltipPlacement } from "antd/es/tooltip"; // antd6: 路径从 lib → es
import { temp } from "../../reducers/setting";
import TagsArea from "../tag/TagsArea";
import type { IStudio } from "../../api/Auth";
import type { ArticleMode } from "../../api/article";
import type { ITagMapData } from "../../api/Tag";
import PaliText from "../general/PaliText";
import { bookMarkColor } from "./utils";
import type { IWbw, IWbwAttachment, TWbwDisplayMode } from "../../types/wbw";

export const PopPlacement = "setting.wbw.pop.placement";

// ─── VideoIcon ────────────────────────────────────────────────────────────────

interface IVideoIcon {
  attachments?: IWbwAttachment[];
}

const VideoIcon = ({ attachments }: IVideoIcon) => {
  const videoList = attachments?.filter((item) =>
    item.content_type?.includes("video")
  );
  if (!videoList?.length) return null;
  return (
    <WbwVideoButton
      video={videoList.map((item) => ({
        videoId: item.id,
        type: item.content_type,
        title: item.title,
      }))}
    />
  );
};

// ─── NoteIcon ─────────────────────────────────────────────────────────────────

interface INoteIcon {
  note?: string;
}

const NoteIcon = ({ note }: INoteIcon) => {
  if (!note?.trim()) return null;
  return (
    <Popover content={note} placement="bottom">
      <InfoCircleOutlined style={{ color: "blue" }} />
    </Popover>
  );
};

// ─── RelationIcon ─────────────────────────────────────────────────────────────

interface IRelationIcon {
  hasRelation?: boolean;
}

const RelationIcon = ({ hasRelation }: IRelationIcon) =>
  hasRelation ? <ApartmentOutlined style={{ color: "blue" }} /> : null;

// ─── BookMarkIcon ─────────────────────────────────────────────────────────────

interface IBookMarkIcon {
  text?: string;
  color: string;
}

const BookMarkIcon = ({ text, color }: IBookMarkIcon) => {
  if (!text?.trim()) return null;
  return (
    <Popover
      content={<Typography.Paragraph copyable>{text}</Typography.Paragraph>}
      placement="bottom"
    >
      <TagTwoTone twoToneColor={color} />
    </Popover>
  );
};

// ─── Types ────────────────────────────────────────────────────────────────────

interface IWidget {
  data: IWbw;
  studio?: IStudio;
  channelId: string;
  display?: TWbwDisplayMode;
  mode?: ArticleMode;
  readonly?: boolean;
  onSave?: (e: IWbw, isPublish: boolean, isPublic: boolean) => void;
}

// ─── WbwPaliWidget ────────────────────────────────────────────────────────────

const WbwPaliWidget = ({
  data,
  channelId,
  mode,
  studio,
  readonly = false,
  onSave,
}: IWidget) => {
  const [popOpen, setPopOpen] = useState(false);
  const [tags, setTags] = useState<ITagMapData[]>();
  const [paliColor, setPaliColor] = useState("unset");

  const divShell = useRef<HTMLDivElement>(null);
  const wbwAnchor = useAppSelector(anchor);
  const addParam = useAppSelector(relationAddParam);
  const wordSn = `${data.book}-${data.para}-${data.sn.join("-")}`;
  const tempSettings = useAppSelector(temp);

  /**
   * 修复1：popOnTop 是纯派生值（来自 tempSettings），无需 state + effect 同步。
   * 直接用 useMemo 在渲染时计算，消除一次 setState-in-effect。
   */
  const popOnTop = useMemo(() => {
    const popSetting = tempSettings?.find((v) => v.key === PopPlacement);
    console.debug("PopPlacement change", popSetting);
    return popSetting?.value === true;
  }, [tempSettings]);

  /**
   * 修复2：popPlacement 用 useState 存储，但只在事件处理器中更新。
   * 在事件处理器（用户点击）里读取 DOM ref 并 setState 完全合法，
   * 不会产生"effect 内 setState"的级联渲染警告。
   */
  const [popPlacement, setPopPlacement] = useState<TooltipPlacement>("bottom");

  const computeAndSetPlacement = useCallback(() => {
    const rightPanel = document.getElementById("article_right_panel");
    const rightPanelWidth = rightPanel?.offsetWidth ?? 0;
    const containerWidth = window.innerWidth - rightPanelWidth;
    const divRight = divShell.current?.getBoundingClientRect().right ?? 0;
    const toDivRight = containerWidth - divRight;
    setPopPlacement(
      popOnTop
        ? toDivRight > 200
          ? "top"
          : "topRight"
        : toDivRight > 200
          ? "bottom"
          : "bottomRight"
    );
  }, [popOnTop]);

  // ── Popover open/close ────────────────────────────────────────────────────
  const popOpenChange = useCallback(
    (open: boolean) => {
      if (open) {
        // 事件处理器中读取 ref + setState，合法，不产生级联渲染
        computeAndSetPlacement();
        setPaliColor("lightblue");
      } else {
        setPaliColor("unset");
      }
      setPopOpen(open);
    },
    [computeAndSetPlacement]
  );

  /**
   * relation 高亮颜色是派生值，用 useMemo 直接计算。
   */
  const relationHighlight = useMemo(() => {
    let grammar = data.case?.value
      ?.replace("v:ind", "v")
      .replace("#", "$")
      .replace(":", "$")
      .replaceAll(".", "")
      .split("$");

    if (data.grammar2?.value) {
      grammar = grammar
        ? [data.grammar2.value, ...grammar]
        : [data.grammar2.value];
    }

    if (!grammar) return false;

    const match = addParam?.relations?.filter((value) => {
      if (!value.to) return false;
      const caseMatch =
        !value.to.case ||
        value.to.case.filter((c) => grammar!.includes(c)).length ===
          value.to.case.length;
      const spellMatch = !value.to.spell || data.real.value === value.to.spell;
      return caseMatch && spellMatch;
    });

    return !!(match && match.length > 0);
  }, [
    addParam?.relations,
    data.case?.value,
    data.grammar2?.value,
    data.real.value,
  ]);

  /**
   * 修复3：popOpen 和 paliColor 的"关闭"逻辑来自 wbwAnchor 的变化。
   * 不能在 effect 里 setState，改为用 useMemo 派生出是否应该强制关闭，
   * 再结合实际的 open state 计算最终值。
   * anchorClosed = true 表示当前 anchor 指向别处，本组件应处于关闭态。
   */
  const anchorClosed = useMemo(() => {
    if (!wbwAnchor) return false;
    return wbwAnchor.id !== wordSn || wbwAnchor.channel !== channelId;
  }, [wbwAnchor, wordSn, channelId]);

  // 最终是否打开：自身 state 为 true 且 anchor 未指向别处
  const resolvedPopOpen = popOpen && !anchorClosed;

  /**
   * 修复5：addParam apply/cancel 时重置 paliColor 并重新打开弹窗。
   * 原来在 effect 里 setState，改为用 useMemo 派生：
   * - forceOpen: 当前 addParam 命令要求重新打开本组件的弹窗
   * - forceColorReset: 当前 addParam 命令要求重置颜色
   */
  const forceOpen = useMemo(() => {
    if (addParam?.command !== "apply" && addParam?.command !== "cancel")
      return false;
    return (
      addParam.src_sn === data.sn.join("-") &&
      addParam.book === data.book &&
      addParam.para === data.para
    );
  }, [addParam, data.book, data.para, data.sn]);

  const forceColorReset = useMemo(() => {
    return addParam?.command === "apply" || addParam?.command === "cancel";
  }, [addParam?.command]);

  // 派生最终 popOpen：forceOpen 优先
  const finalPopOpen = forceOpen ? true : resolvedPopOpen;

  // 当 forceOpen 激活时，触发 dispatch（side effect 仍需 effect，但 setState 已移除）
  useEffect(() => {
    if (forceOpen) {
      store.dispatch(
        add({
          book: data.book,
          para: data.para,
          src_sn: data.sn.join("-"),
          command: "finish",
        })
      );
    }
    // 仅在 forceOpen 变为 true 时触发一次，dispatch 不是 setState，合法
  }, [forceOpen, data.book, data.para, data.sn]);

  // paliColor 合并：交互状态优先，forceColorReset/anchorClosed 时重置，其次 relation 高亮
  const resolvedPaliColor =
    anchorClosed || forceColorReset
      ? relationHighlight
        ? "greenyellow"
        : "unset"
      : paliColor !== "unset"
        ? paliColor
        : relationHighlight
          ? "greenyellow"
          : "unset";

  // ── Sub-components ────────────────────────────────────────────────────────

  const wbwDialog = () => (
    <WbwDetail
      data={data}
      visible={finalPopOpen}
      popIsTop={popOnTop}
      readonly={readonly}
      onClose={() => {
        setPaliColor("unset");
        setPopOpen(false);
      }}
      onSave={(e: IWbw, isPublish: boolean, isPublic: boolean) => {
        onSave?.(e, isPublish, isPublic);
        setPopOpen(false);
        setPaliColor("unset");
      }}
      onAttachmentSelectOpen={(open: boolean) => {
        setPopOpen(!open);
      }}
      onTagCreate={(newTags: ITagMapData[]) => {
        setTags(newTags);
      }}
    />
  );

  // ── Pali class & padding ──────────────────────────────────────────────────
  let classPali = "pali";
  if (data.style?.value === "note") {
    classPali = "wbw_note";
  } else if (data.style?.value === "bld" && !data.word.value.includes("{")) {
    classPali = "pali wbw_bold";
  }

  const padding =
    typeof data.real !== "undefined" && data.real.value !== ""
      ? "4px"
      : "4px 0";

  // ── Pali node ─────────────────────────────────────────────────────────────
  let pali: React.ReactNode;
  if (data.word.value.includes("}")) {
    const paliArray = data.word.value.replace("{", "").split("}");
    pali = (
      <>
        <span style={{ fontWeight: 700 }}>
          <PaliText
            style={{ color: "brown" }}
            text={paliArray[0]}
            termToLocal={false}
          />
        </span>
        <PaliText
          style={{ color: "brown" }}
          text={paliArray[1]}
          termToLocal={false}
        />
      </>
    );
  } else {
    pali = (
      <PaliText
        style={{ color: "brown" }}
        text={data.word.value}
        termToLocal={false}
      />
    );
  }

  const paliWord = (
    <span
      className={classPali}
      style={{ backgroundColor: resolvedPaliColor, padding, borderRadius: 5 }}
      onClick={() => {
        if (typeof data.real?.value === "string") {
          store.dispatch(lookup(data.real.value));
        }
      }}
    >
      {pali}
    </span>
  );

  // ── Render ────────────────────────────────────────────────────────────────

  if (typeof data.real !== "undefined" && data.real.value !== "") {
    // 非标点符号
    return (
      <div className="pali_shell" ref={divShell}>
        <div style={{ position: "absolute", marginTop: -24 }}>
          <TagsArea
            resId={data.uid}
            resType="wbw"
            selectorTitle={data.word.value}
            data={tags}
            max={1}
          />
        </div>

        <span className="pali_shell_spell">
          {data.grammarId ? (
            <span
              onClick={() => store.dispatch(grammarId(data.grammarId))}
              style={{ cursor: "pointer" }}
            >
              <QuestionCircleOutlined style={{ color: "blue" }} />
            </span>
          ) : null}

          <Popover
            content={wbwDialog}
            placement={popPlacement}
            trigger="click"
            open={finalPopOpen}
          >
            <span
              onClick={() => {
                popOpenChange(true);
                store.dispatch(showWbw({ id: wordSn, channel: channelId }));
              }}
            >
              {mode === "wbw" ? (
                paliWord
              ) : (
                <span className="edit_icon">
                  <EditOutlined style={{ cursor: "pointer" }} />
                </span>
              )}
            </span>
          </Popover>

          {mode === "edit" ? paliWord : null}
        </span>

        {/*
          antd6: Space 组件内部渲染方式调整，子节点返回 null 可能导致多余间距。
          改用 inline-flex 容器替代 <Space>，更可预期地处理动态显隐子节点。
        */}
        <span style={{ display: "inline-flex", gap: 4, alignItems: "center" }}>
          <VideoIcon attachments={data.attachments} />
          <NoteIcon note={data.note?.value ?? undefined} />
          <BookMarkIcon
            text={data.bookMarkText?.value ?? undefined}
            color={
              data.bookMarkColor?.value
                ? bookMarkColor[data.bookMarkColor.value]
                : "white"
            }
          />
          <RelationIcon hasRelation={!!data.relation} />
          <WbwPaliDiscussionIcon
            data={data}
            studio={studio}
            channelId={channelId}
          />
        </span>
      </div>
    );
  }

  // 标点符号
  return (
    <div className="pali_shell" style={{ cursor: "unset" }}>
      {data.bookName ? (
        <>
          {/** TODO reload
         * <ParaLinkCtl
          title={data.word.value}
          bookName={data.bookName}
          paragraphs={data.word?.value}
        />
         */}
        </>
      ) : (
        paliWord
      )}
    </div>
  );
};

export default WbwPaliWidget;

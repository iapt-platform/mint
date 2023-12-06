import { useEffect, useRef, useState } from "react";
import { Button, Popover, Space, Typography } from "antd";
import {
  TagTwoTone,
  InfoCircleOutlined,
  ApartmentOutlined,
  EditOutlined,
} from "@ant-design/icons";

import "./wbw.css";
import WbwDetail from "./WbwDetail";
import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { bookMarkColor } from "./WbwDetailBookMark";
import WbwVideoButton from "./WbwVideoButton";
import CommentBox from "../../discussion/DiscussionDrawer";
import PaliText from "./PaliText";
import store from "../../../store";
import { lookup } from "../../../reducers/command";
import { useAppSelector } from "../../../hooks";
import { add, relationAddParam } from "../../../reducers/relation-add";
import { ArticleMode } from "../../article/Article";
import { anchor, showWbw } from "../../../reducers/wbw";
import { CommentOutlinedIcon } from "../../../assets/icon";
import { ParaLinkCtl } from "../ParaLink";

const { Paragraph } = Typography;
interface IWidget {
  data: IWbw;
  channelId: string;
  display?: TWbwDisplayMode;
  mode?: ArticleMode;
  onSave?: Function;
}
const WbwPaliWidget = ({ data, channelId, mode, display, onSave }: IWidget) => {
  const [popOpen, setPopOpen] = useState(false);
  const [paliColor, setPaliColor] = useState("unset");
  const [hasComment, setHasComment] = useState(data.hasComment);
  const divShell = useRef<HTMLDivElement>(null);
  const wbwAnchor = useAppSelector(anchor);
  const addParam = useAppSelector(relationAddParam);
  const wordSn = `${data.book}-${data.para}-${data.sn.join("-")}`;
  useEffect(() => {
    if (wbwAnchor) {
      if (wbwAnchor.id !== wordSn || wbwAnchor.channel !== channelId) {
        popOpenChange(false);
      }
    }
  }, [channelId, wbwAnchor, wordSn]);

  /**
   * 处理 relation 链接事件
   * 高亮可能的单词
   */
  useEffect(() => {
    let grammar = data.case?.value
      ?.replace("#", "$")
      .replaceAll(".", "")
      .split("$");
    if (data.grammar2?.value) {
      if (grammar) {
        grammar = [data.grammar2?.value, ...grammar];
      } else {
        grammar = [data.grammar2?.value];
      }
    }
    if (typeof grammar === "undefined") {
      return;
    }
    const match = addParam?.relations?.filter((value) => {
      let caseMatch = true;
      let spellMatch = true;
      if (!value.to) {
        return false;
      }
      if (value.to?.case) {
        let matchCount = 0;
        if (grammar) {
          for (const iterator of value.to.case) {
            if (grammar?.includes(iterator)) {
              matchCount++;
            }
          }
        }
        if (matchCount !== value.to.case.length) {
          caseMatch = false;
        }
      }
      if (value.from?.spell) {
        if (data.real.value !== value.from?.spell) {
          spellMatch = false;
        }
      }
      return caseMatch && spellMatch;
    });
    if (match && match.length > 0) {
      setPaliColor("greenyellow");
    }
  }, [
    addParam?.relations,
    data.case?.value,
    data.grammar2?.value,
    data.real.value,
  ]);
  /**
   * 点击连接或取消后，打开弹窗
   */
  useEffect(() => {
    if (addParam?.command === "apply" || addParam?.command === "cancel") {
      setPaliColor("unset");
      if (
        addParam.src_sn === data.sn.join("-") &&
        addParam.book === data.book &&
        addParam.para === data.para
      ) {
        setPopOpen(true);
        store.dispatch(
          add({
            book: data.book,
            para: data.para,
            src_sn: data.sn.join("-"),
            command: "finish",
          })
        );
      }
    }
  }, [
    addParam?.book,
    addParam?.command,
    addParam?.para,
    addParam?.src_sn,
    data.book,
    data.para,
    data.sn,
  ]);

  const popOpenChange = (open: boolean) => {
    if (open) {
      setPaliColor("lightblue");
    } else {
      setPaliColor("unset");
    }
    setPopOpen(open);
  };

  const wbwDetail = (
    <WbwDetail
      data={data}
      onClose={() => {
        setPaliColor("unset");
        setPopOpen(false);
      }}
      onSave={(e: IWbw, isPublish: boolean) => {
        if (typeof onSave !== "undefined") {
          onSave(e, isPublish);
          setPopOpen(false);
          setPaliColor("unset");
        }
      }}
      onCommentCountChange={(count: number) => {
        if (count > 0) {
          setHasComment(true);
        } else {
          setHasComment(false);
        }
      }}
    />
  );

  const noteIcon = data.note?.value ? (
    data.note.value.trim() !== "" ? (
      <Popover content={data.note?.value} placement="bottom">
        <InfoCircleOutlined style={{ color: "blue" }} />
      </Popover>
    ) : undefined
  ) : undefined;
  const color = data.bookMarkColor?.value
    ? bookMarkColor[data.bookMarkColor.value]
    : "white";

  //生成视频播放按钮
  const videoList = data.attachments?.filter((item) =>
    item.content_type?.includes("video")
  );
  const videoIcon = videoList ? (
    <WbwVideoButton
      video={videoList?.map((item) => {
        return {
          videoId: item.id,
          type: item.content_type,
          title: item.title,
        };
      })}
    />
  ) : undefined;

  const relationIcon = data.relation ? (
    <ApartmentOutlined style={{ color: "blue" }} />
  ) : undefined;

  const bookMarkIcon =
    data.bookMarkText?.value && data.bookMarkText.value.trim() !== "" ? (
      <Popover
        content={<Paragraph copyable>{data.bookMarkText.value}</Paragraph>}
        placement="bottom"
      >
        <TagTwoTone twoToneColor={color} />
      </Popover>
    ) : undefined;
  let classPali: string;
  switch (data.style?.value) {
    case "note":
      classPali = "wbw_note";
      break;
    case "bld":
      classPali = "pali wbw_bold";
      break;
    default:
      classPali = "pali";
      break;
  }
  let padding: string;
  if (typeof data.real !== "undefined" && data.real.value !== "") {
    padding = "4px";
  } else {
    padding = "4px 0";
  }
  let pali = (
    <PaliText
      style={{ color: "brown" }}
      text={data.word.value}
      termToLocal={false}
    />
  );
  if (data.word.value.indexOf("}") >= 0) {
    const paliArray = data.word.value?.replace("{", "").split("}");
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
  }
  const paliWord = (
    <span
      className={classPali}
      style={{
        backgroundColor: paliColor,
        padding: padding,
        borderRadius: 5,
      }}
      onClick={() => {
        //发送点词查询消息
        if (typeof data.real?.value === "string") {
          store.dispatch(lookup(data.real.value));
        }
      }}
    >
      {pali}
    </span>
  );

  let commentShellStyle: React.CSSProperties = {
    display: "inline-block",
  };

  const discussionIcon = hasComment ? (
    <div style={commentShellStyle}>
      <CommentBox
        resId={data.uid}
        resType="wbw"
        trigger={
          <Button icon={<CommentOutlinedIcon />} type="text" title="讨论" />
        }
        onCommentCountChange={(count: number) => {
          if (count > 0) {
            setHasComment(true);
          } else {
            setHasComment(false);
          }
        }}
      />
    </div>
  ) : undefined;

  if (typeof data.real !== "undefined" && data.real.value !== "") {
    //非标点符号
    //单词在右侧时，为了不遮挡字典，Popover向左移动
    const rightPanel = document.getElementById("article_right_panel");
    const rightPanelWidth = rightPanel ? rightPanel.offsetWidth : 0;
    const containerWidth = window.innerWidth - rightPanelWidth;
    const divRight = divShell.current?.getBoundingClientRect().right;
    const toDivRight = divRight ? containerWidth - divRight : 0;

    return (
      <div className="pali_shell" ref={divShell}>
        <span className="pali_shell_spell">
          {mode === "edit" ? paliWord : ""}
          <Popover
            content={wbwDetail}
            placement={toDivRight > 200 ? "bottom" : "bottomRight"}
            trigger="click"
            open={popOpen}
          >
            <span
              onClick={() => {
                popOpenChange(true);
                store.dispatch(
                  showWbw({
                    id: wordSn,
                    channel: channelId,
                  })
                );
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
        </span>
        <Space>
          {videoIcon}
          {noteIcon}
          {bookMarkIcon}
          {relationIcon}
          {discussionIcon}
        </Space>
      </div>
    );
  } else {
    //标点符号
    return (
      <div className="pali_shell" style={{ cursor: "unset" }}>
        {data.type?.value === ":cs.para:" ? (
          <ParaLinkCtl
            title={data.word.value}
            bookName={data.grammar?.value}
            paragraphs={data.word?.value}
          />
        ) : (
          paliWord
        )}
      </div>
    );
  }
};

export default WbwPaliWidget;

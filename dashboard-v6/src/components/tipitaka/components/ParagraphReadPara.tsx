import { Flex } from "antd";
import { useSetting } from "../../../hooks/useSetting";
import MdOrigin from "../../sentence/components/MdOrigin";
import MdTranslation from "../../sentence/components/MdTranslation";
import type { IWidgetSentEditInner } from "../../sentence/SentEdit";
import CommentaryPad from "./CommentaryPad";
import { SENT_HIGHLIGHT_BG, useSentHighlight } from "./useSentHighlight";

interface IWidget {
  data?: IWidgetSentEditInner[];
}
const ParagraphReadPara = ({ data }: IWidget) => {
  const direction = useSetting("setting.layout.direction");
  const layoutCommentary = useSetting("setting.layout.commentary");
  console.debug("direction", direction);
  const { rootRef, isHighlighted } = useSentHighlight();

  const channels: string[] = [];
  data?.forEach((value) => {
    value.translation?.forEach((trans) => {
      if (!channels.includes(trans.channel.id)) {
        channels.push(trans.channel.id);
      }
    });
  });

  let commentaries: number = 0;
  data?.forEach((value) => {
    value.commentaries?.forEach((comm) => {
      if (comm.content && comm.content?.length > 0) {
        commentaries++;
      }
    });
  });

  return (
    <Flex vertical={layoutCommentary === "column"}>
      <Flex gap="middle" vertical={direction === "column"} style={{ flex: 5 }}>
        {/**原文区 */}
        <div className="sent_read" style={{ flex: 5, padding: 4 }}>
          {data?.map((item) => {
            return item.origin?.map((org, id) => {
              return <MdOrigin text={org.html} key={id} />;
            });
          })}
        </div>
        {/**译文区 */}
        <div
          ref={rootRef}
          className="sent_read"
          style={{ display: "block", flex: 5, padding: 4 }}
        >
          {channels.map((channel) => {
            return (
              <div>
                {data?.map((item) => {
                  return item.translation?.map((trans, id) => {
                    if (trans.channel.id === channel) {
                      // 整段排版里句子是行内的：套一层 span 承载定位与高亮
                      return (
                        <span
                          key={id}
                          data-sent-uid={trans.id}
                          style={{
                            transition: "background-color 0.3s",
                            backgroundColor: isHighlighted(trans.id)
                              ? SENT_HIGHLIGHT_BG
                              : undefined,
                          }}
                        >
                          <MdTranslation text={trans.html} />
                        </span>
                      );
                    }
                  });
                })}
              </div>
            );
          })}
        </div>
      </Flex>
      {/**注疏区 */}
      {commentaries > 0 && (
        <div style={{ flex: 5 }}>
          <CommentaryPad>
            {data?.map((item) => {
              return item.commentaries?.map((item, id) => {
                return <MdTranslation text={item.html} key={id} />;
              });
            })}
          </CommentaryPad>
        </div>
      )}
    </Flex>
  );
};

export default ParagraphReadPara;

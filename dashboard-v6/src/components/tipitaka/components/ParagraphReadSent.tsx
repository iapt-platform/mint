import { Flex } from "antd";
import { useSetting } from "../../../hooks/useSetting";
import MdOrigin from "../../sentence/components/MdOrigin";
import MdTranslation from "../../sentence/components/MdTranslation";
import type { IWidgetSentEditInner } from "../../sentence/SentEdit";
import CommentaryPad from "./CommentaryPad";

interface IWidget {
  data?: IWidgetSentEditInner[];
}
const ParagraphRead = ({ data }: IWidget) => {
  const direction = useSetting("setting.layout.direction");
  const layoutCommentary = useSetting("setting.layout.commentary");
  console.debug("direction", direction);

  const channels: string[] = [];
  data?.forEach((value) => {
    value.translation?.forEach((trans) => {
      if (!channels.includes(trans.channel.id)) {
        channels.push(trans.channel.id);
      }
    });
  });

  return (
    <div>
      {data?.map((item) => {
        return (
          <Flex vertical={layoutCommentary === "row"}>
            <Flex
              gap="middle"
              vertical={direction === "row"}
              style={{ flex: 5 }}
            >
              {/**原文区 */}
              <div className="sent_read" style={{ flex: 5, padding: 4 }}>
                {item.origin?.map((org, id) => {
                  return <MdOrigin text={org.html} key={id} />;
                })}
              </div>
              {/**译文区 */}
              <div
                className="sent_read"
                style={{ display: "block", flex: 5, padding: 4 }}
              >
                {item.translation?.map((tran, id) => {
                  return (
                    <div>
                      <MdTranslation text={tran.html} key={id} />
                    </div>
                  );
                })}
              </div>
            </Flex>
            {/**注疏区 */}
            {item.commentaries && item.commentaries?.length > 0 && (
              <div style={{ flex: 5 }}>
                <CommentaryPad>
                  {item.commentaries?.map((item, id) => {
                    return <MdTranslation text={item.html} key={id} />;
                  })}
                </CommentaryPad>
              </div>
            )}
          </Flex>
        );
      })}
    </div>
  );
};

export default ParagraphRead;

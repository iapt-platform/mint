import MdOrigin from "../../sentence/components/MdOrigin";
import MdTranslation from "../../sentence/components/MdTranslation";
import type { IWidgetSentEditInner } from "../../sentence/SentEdit";

interface IWidget {
  data?: IWidgetSentEditInner[];
}
const ParagraphRead = ({ data }: IWidget) => {
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
      <div style={{ display: "flex" }}>
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
          className="sent_read"
          style={{ display: "flex", flex: 5, padding: 4 }}
        >
          {channels.map((channel) => {
            return (
              <div>
                {data?.map((item) => {
                  return item.translation?.map((trans, id) => {
                    if (trans.channel.id === channel) {
                      return <MdTranslation text={trans.html} key={id} />;
                    } else {
                      return <span>no data</span>;
                    }
                  });
                })}
              </div>
            );
          })}
        </div>
      </div>
      {/**注疏区 */}
      <div></div>
    </div>
  );
};

export default ParagraphRead;

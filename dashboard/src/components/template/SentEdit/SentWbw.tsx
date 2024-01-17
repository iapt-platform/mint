import { List, message } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../../request";
import { ISentenceWbwListResponse } from "../../api/Corpus";
import { IWidgetSentEditInner, SentEditInner } from "../SentEdit";
import { useAppSelector } from "../../../hooks";
import { courseInfo, memberInfo } from "../../../reducers/current-course";
import { courseUser } from "../../../reducers/course-user";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  reload?: boolean;
  wbwProgress?: boolean;
  onReload?: Function;
}
const SentWbwWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  channelsId,
  reload = false,
  wbwProgress = false,
  onReload,
}: IWidget) => {
  const [initLoading, setInitLoading] = useState(true);
  const [sentData, setSentData] = useState<IWidgetSentEditInner[]>([]);
  const course = useAppSelector(courseInfo);
  const courseMember = useAppSelector(memberInfo);

  const myCourse = useAppSelector(courseUser);

  const load = () => {
    let url = `/v2/wbw-sentence?view=sent-can-read`;
    url += `&book=${book}&para=${para}&wordStart=${wordStart}&wordEnd=${wordEnd}`;

    console.debug("wbw sentence load", myCourse, course);
    if (myCourse && course) {
      url += `&course=${course.courseId}`;
      if (myCourse.role === "student") {
        url += `&channels=${course.channelId}`;
      } else if (courseMember) {
        console.debug("course member", courseMember);
        const channels = courseMember
          .filter((value) => typeof value.channel_id === "string")
          .map((item) => item.channel_id);
        url += `&channels=${channels.join(",")}`;
      }
    } else {
      if (channelsId && channelsId.length > 0) {
        url += `&exclude=${channelsId[0]}`;
      }
    }

    console.log("wbw sentence url", url);
    get<ISentenceWbwListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("sim load", json.data.count);
          setSentData(json.data.rows);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setInitLoading(false);
        if (reload && typeof onReload !== "undefined") {
          onReload();
        }
      });
  };
  useEffect(() => {
    load();
  }, []);

  return (
    <>
      <List
        loading={initLoading}
        itemLayout="horizontal"
        split={false}
        dataSource={sentData}
        renderItem={(item, index) => (
          <List.Item key={index}>
            <SentEditInner {...item} wbwProgress={wbwProgress} />
          </List.Item>
        )}
      />
    </>
  );
};

export default SentWbwWidget;

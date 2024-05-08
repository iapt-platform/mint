import { List, Space, message } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../../request";
import { ISentenceWbwListResponse } from "../../api/Corpus";
import { IWidgetSentEditInner, SentEditInner } from "../SentEdit";
import { useAppSelector } from "../../../hooks";
import { courseInfo, memberInfo } from "../../../reducers/current-course";
import { courseUser } from "../../../reducers/course-user";
import User, { IUser } from "../../auth/User";

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
        //学生，仅列出答案channel
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

    console.log("wbw sentence api request", url);
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

  //没交作业的人

  let nonWbwUser: IUser[] = [];
  if (myCourse && course && myCourse.role !== "student" && courseMember) {
    const hasWbwUsers = sentData.map((item) =>
      item.translation ? item.translation[0].studio : undefined
    );
    courseMember
      .filter((value) => value.role === "student")
      .forEach((value) => {
        const curr = hasWbwUsers.find((value1) => value1?.id === value.user_id);
        if (!curr && value.user) {
          nonWbwUser.push(value.user);
        }
      });
  }
  console.debug("没交作业", courseMember, sentData, nonWbwUser);
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
      <div>
        {nonWbwUser.length > 0 ? (
          <Space>
            {"没交作业："}
            {nonWbwUser.map((item, id) => {
              return <User {...item} />;
            })}
          </Space>
        ) : undefined}
      </div>
    </>
  );
};

export default SentWbwWidget;

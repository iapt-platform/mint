import { Button, List, Select, Space, message } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { ISentenceWbwListResponse } from "../../api/Corpus";
import { ISentence, IWidgetSentEditInner, SentEditInner } from "../SentEdit";
import { useAppSelector } from "../../../hooks";
import { courseInfo, memberInfo } from "../../../reducers/current-course";
import { courseUser } from "../../../reducers/course-user";
import User, { IUser } from "../../auth/User";
import { IWbw } from "../Wbw/WbwWord";
import { getWbwProgress } from "../WbwSent";
import moment from "moment";

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
  const [sentData, setSentData] = useState<IWidgetSentEditInner[]>([]);
  const [answer, setAnswer] = useState<ISentence>();
  const [loading, setLoading] = useState<boolean>(false);
  const [order, setOrder] = useState("progress");
  const course = useAppSelector(courseInfo);
  const courseMember = useAppSelector(memberInfo);

  const myCourse = useAppSelector(courseUser);

  let isCourse: boolean = false;
  if (myCourse && course) {
    isCourse = true;
  }

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
        //管理者，助教，列出学生作业
        console.debug("course member", courseMember);
        /*
        const channels = courseMember
          .filter((value) => typeof value.channel_id === "string")
          .map((item) => item.channel_id);
        url += `&channels=${channels.join(",")}`;
        */
      }
    } else {
      if (channelsId && channelsId.length > 0) {
        url += `&exclude=${channelsId[0]}`;
      }
    }

    setLoading(true);
    console.info("wbw sentence api request", url);
    get<ISentenceWbwListResponse>(url)
      .then((json) => {
        console.info("wbw sentence api response", json);
        if (json.ok) {
          console.debug("wbw sentence course", course);
          let response: IWidgetSentEditInner[] = json.data.rows;
          if (course && myCourse && myCourse.role !== "student") {
            response = json.data.rows.filter((value) =>
              value.translation
                ? value.translation[0].channel.id !== course.channelId
                : true
            );
          }
          response.forEach(
            (
              value: IWidgetSentEditInner,
              index: number,
              array: IWidgetSentEditInner[]
            ) => {
              if (value.origin) {
                if (value.origin.length > 0) {
                  if (value.origin[0].content) {
                    const json: IWbw[] = JSON.parse(value.origin[0].content);
                    const progress = getWbwProgress(json);
                    array[index].wbwProgress = progress;
                  }
                }
              }
            }
          );
          console.debug("response with progress", response);
          setSentData(response);
          if (myCourse && course) {
            const answerData = json.data.rows.find((value) =>
              value.origin
                ? value.origin[0].channel.id === course?.channelId
                : false
            );
            if (answerData?.origin) {
              setAnswer(answerData.origin[0]);
              console.debug("answer", answerData.origin[0]);
            }
          }
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
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
  const isCourseAnswer = myCourse && course && myCourse.role !== "student";
  if (isCourseAnswer && courseMember) {
    const hasWbwUsers = sentData.map((item) =>
      item.translation ? item.translation[0].studio : undefined
    );
    courseMember
      .filter(
        (value) =>
          value.role === "student" &&
          (value.status === "joined" ||
            value.status === "accepted" ||
            value.status === "agreed")
      )
      .forEach((value) => {
        const curr = hasWbwUsers.find((value1) => value1?.id === value.user_id);
        if (!curr && value.user) {
          nonWbwUser.push(value.user);
        }
      });
  }
  console.debug("没交作业", courseMember, sentData, nonWbwUser);

  let aaa = [...sentData].sort(
    (a: IWidgetSentEditInner, b: IWidgetSentEditInner) => {
      switch (order) {
        case "progress":
          if (a.wbwProgress && b.wbwProgress) {
            return b.wbwProgress - a.wbwProgress;
          } else {
            return 0;
          }
          break;
        case "updated":
          if (a.origin && b.origin) {
            if (
              moment(b.origin[0].updateAt).isBefore(
                moment(a.origin[0].updateAt)
              )
            ) {
              return 1;
            } else {
              return -1;
            }
          } else {
            return 0;
          }
          break;
      }
      if (a.wbwProgress && b.wbwProgress) {
        return b.wbwProgress - a.wbwProgress;
      } else {
        return 0;
      }
    }
  );

  return (
    <>
      <List
        loading={loading}
        header={
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <span></span>
            <Space>
              <Select
                disabled
                defaultValue={"progress"}
                options={[
                  { value: "progress", label: "完成度" },
                  { value: "updated", label: "更新时间" },
                ]}
                onChange={(value: string) => setOrder(value)}
              />
              <Button
                type="link"
                shape="round"
                icon={<ReloadOutlined />}
                onClick={() => load()}
              />
            </Space>
          </div>
        }
        itemLayout="horizontal"
        split={false}
        dataSource={aaa}
        renderItem={(item, index) => (
          <List.Item key={index}>
            <SentEditInner
              {...item}
              readonly={isCourse}
              answer={answer}
              showWbwProgress={isCourse ?? wbwProgress}
            />
          </List.Item>
        )}
      />
      <div>
        {isCourseAnswer ? (
          <Space style={{ flexWrap: "wrap" }}>
            {"无作业："}
            {nonWbwUser.length > 0
              ? nonWbwUser.map((item, id) => {
                  return <User {...item} />;
                })
              : "无"}
          </Space>
        ) : undefined}
      </div>
    </>
  );
};

export default SentWbwWidget;

import { Button, List, Select, Space, message } from "antd";
import { useCallback, useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../request";
import type { ISentence, ISentenceWbwListResponse } from "../../api/sentence";

import { useAppSelector } from "../../hooks";
import { courseInfo, memberInfo } from "../../reducers/current-course";
import { courseUser } from "../../reducers/course-user";

import moment from "dayjs";
import type { IUser } from "../../api/Auth";
import User from "../auth/User";
import { getWbwProgress } from "../wbw/utils";
import { SentEditInner, type IWidgetSentEditInner } from "./SentEdit";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  reload?: boolean;
  wbwProgress?: boolean;
}
const SentWbwWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  channelsId,
  wbwProgress = false,
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

  const load = useCallback(async () => {
    let url = `/v2/wbw-sentence?view=sent-can-read`;
    url += `&book=${book}&para=${para}&wordStart=${wordStart}&wordEnd=${wordEnd}`;

    if (myCourse && course) {
      url += `&course=${course.courseId}`;
      if (myCourse.role === "student") {
        url += `&channels=${course.channelId}`;
      }
    } else {
      if (channelsId?.length && channelsId?.length > 0) {
        url += `&exclude=${channelsId[0]}`;
      }
    }

    setLoading(true);

    try {
      const json = await get<ISentenceWbwListResponse>(url);

      if (!json.ok) {
        message.error(json.message);
        return;
      }

      let response = json.data.rows;

      if (course && myCourse && myCourse.role !== "student") {
        response = response.filter((v) =>
          v.translation
            ? v.translation[0].channel.id !== course.channelId
            : true
        );
      }

      response.forEach((value, index, array) => {
        if (value.origin?.[0]?.content) {
          const parsed = JSON.parse(value.origin[0].content);
          array[index].wbwProgress = getWbwProgress(parsed);
        }
      });

      setSentData(response);

      if (myCourse && course) {
        const answerData = json.data.rows.find(
          (v) => v.origin?.[0].channel.id === course.channelId
        );

        if (answerData?.origin) {
          setAnswer(answerData.origin[0]);
        }
      }
    } finally {
      setLoading(false);
    }
  }, [book, para, wordStart, wordEnd, myCourse, course, channelsId]);

  useEffect(() => {
    load();
  }, [load]);

  //没交作业的人

  const nonWbwUser: IUser[] = [];
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

  const aaa = [...sentData].sort(
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
                onClick={load}
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
                  return <User key={id} {...item} />;
                })
              : "无"}
          </Space>
        ) : undefined}
      </div>
    </>
  );
};

export default SentWbwWidget;

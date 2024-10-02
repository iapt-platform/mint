//课程详情页面
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Divider, message } from "antd";

import CourseIntro from "../../../components/course/CourseIntro";
import TextBook from "../../../components/course/TextBook";
import { get } from "../../../request";
import {
  ICourseDataResponse,
  ICourseResponse,
} from "../../../components/api/Course";
import CourseHead from "../../../components/course/CourseHead";
import ArticleSkeleton from "../../../components/article/ArticleSkeleton";
import ErrorResult from "../../../components/general/ErrorResult";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const [courseInfo, setCourseInfo] = useState<ICourseDataResponse>();
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  useEffect(() => {
    const url = `/v2/course/${id}`;
    console.info("course url", url);
    setLoading(true);
    get<ICourseResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("api response", json.data);
          setCourseInfo(json.data);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => setLoading(false))
      .catch((e) => {
        console.error(e);
        setErrorCode(e);
      });
  }, [id]);
  return (
    <>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <div>
          <CourseHead data={courseInfo} />
          <Divider />
          <CourseIntro intro={courseInfo?.content} />
          <Divider />
          <TextBook
            anthologyId={courseInfo?.anthology_id}
            courseId={courseInfo?.id}
          />
        </div>
      )}
    </>
  );
};

export default Widget;

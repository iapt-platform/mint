// /src/hooks/useCourse.ts

/**
 * useCourse
 *
 * 获取单个课程详情
 *
 * @param courseId 课程 ID
 *
 * @returns
 *   - data         课程数据，未请求或失败时为 null
 *   - loading      请求进行中
 *   - errorCode    HTTP 错误码，无错误时为 null
 *   - errorMessage 后端错误信息，无错误时为 null
 *   - refresh      手动重新请求
 *
 * @example
 * const { data, loading, errorCode } = useCourse(id);
 */

import { useState, useEffect, useCallback } from "react";

import { fetchCourse } from "../../../api/course";
import type { ICourseDataResponse } from "../../../api/course";
import { HttpError } from "../../../request";

interface IUseCourseReturn {
  data: ICourseDataResponse | null;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useCourse = (courseId?: string): IUseCourseReturn => {
  const [data, setData] = useState<ICourseDataResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!courseId) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchCourse(courseId);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(400);
          setErrorMessage(res.message);
          return;
        }

        setData(res.data);
      } catch (e) {
        console.error("course fetch", e);
        if (!active) return;
        if (e instanceof HttpError) {
          setErrorCode(e.status);
          setErrorMessage(e.message);
        } else {
          setErrorCode(0);
          setErrorMessage("Network error");
        }
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [courseId, tick]);

  return { data, loading, errorCode, errorMessage, refresh };
};

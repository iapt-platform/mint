import { useEffect, useState } from "react";
import { get } from "../../request";
import { IUserStatisticResponse } from "../api/Exp";
import { Skeleton } from "antd";

interface IWidget {
  userName?: string;
}
const ExpTime = ({ userName }: IWidget) => {
  const [expSum, setExpSum] = useState<number>();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (typeof userName === "undefined") {
      return;
    }
    const url = `/v2/user-statistic/${userName}?view=exp-sum`;
    console.info("api request", url);
    setLoading(true);
    get<IUserStatisticResponse>(url)
      .then((json) => {
        console.debug("api response", json);
        if (json.ok) {
          setExpSum(Math.ceil(json.data.exp.sum / 1000 / 60 / 60));
        }
      })
      .finally(() => {
        setLoading(false);
      })
      .catch((e) => console.error(e));
  }, [userName]);
  return loading ? (
    <Skeleton.Button active={true} size={"small"} shape={"default"} />
  ) : (
    <>
      {expSum}
      {"小时"}
    </>
  );
};

export default ExpTime;

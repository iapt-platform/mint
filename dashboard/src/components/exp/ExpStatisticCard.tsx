import { StatisticCard } from "@ant-design/pro-components";
import { message } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import { IUserStatisticResponse } from "../api/Exp";
import ExpPie, { IPieData } from "./ExpPie";

const { Divider } = StatisticCard;

interface IWidget {
  studioName?: string;
}
const ExpStatisticCardWidget = ({ studioName }: IWidget) => {
  const intl = useIntl();
  const [expSum, setExpSum] = useState<number>();
  const [wbwCount, setWbwCount] = useState<number>();
  const [lookupCount, setLookupCount] = useState<number>();
  const [translationCount, setTranslationCount] = useState<number>();
  const [translationPieData, setTranslationPieData] = useState<IPieData[]>();
  const [termCount, setTermCount] = useState<number>();
  const [termPieData, setTermPieData] = useState<IPieData[]>();
  const [dictCount, setDictCount] = useState<number>();
  useEffect(() => {
    const url = `/v2/user-statistic/${studioName}`;
    console.info("api request", url);
    get<IUserStatisticResponse>(url).then((json) => {
      if (json.ok) {
        setExpSum(Math.ceil(json.data.exp.sum / 1000 / 60 / 60));
        setWbwCount(json.data.wbw.count);
        setLookupCount(json.data.lookup.count);
        setTranslationCount(json.data.translation.count);
        setTranslationPieData([
          { type: "公开", value: json.data.translation.count_pub },
          {
            type: "未公开",
            value:
              json.data.translation.count - json.data.translation.count_pub,
          },
        ]);
        setTermCount(json.data.term.count);
        setTermPieData([
          { type: "百科", value: json.data.term.count_with_note },
          {
            type: "仅术语",
            value: json.data.term.count - json.data.term.count_with_note,
          },
        ]);
        setDictCount(json.data.dict.count);
      } else {
        message.error(json.message);
      }
    });
  }, [studioName]);

  return (
    <StatisticCard.Group>
      <StatisticCard
        statistic={{
          title: "总经验",
          tip: "帮助文字",
          value: expSum,
          suffix: "小时",
        }}
      />
      <Divider />
      <StatisticCard
        statistic={{
          title: "逐词解析",
          value: wbwCount,
          suffix: "词",
        }}
      />
      <StatisticCard
        statistic={{
          title: intl.formatMessage({
            id: "buttons.lookup",
          }),
          value: lookupCount,
          suffix: "次",
        }}
      />
      <StatisticCard
        statistic={{
          title: "译文",
          value: translationCount,
          suffix: "句",
        }}
        chart={<ExpPie data={translationPieData} />}
      />
      <StatisticCard
        statistic={{
          title: "术语",
          value: termCount,
          suffix: "词",
        }}
        chart={<ExpPie data={termPieData} />}
      />
      <StatisticCard
        statistic={{
          title: "单词本",
          value: dictCount,
          suffix: "词",
        }}
      />
    </StatisticCard.Group>
  );
};

export default ExpStatisticCardWidget;

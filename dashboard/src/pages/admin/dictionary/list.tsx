import { ProCard, StatisticCard } from "@ant-design/pro-components";
import { useEffect, useState } from "react";
import UserDictList from "../../../components/dict/UserDictTable";
import { get } from "../../../request";

const { Statistic } = StatisticCard;

interface IDict {
  key: string;
  title: string;
  count: number;
  vocabulary: number;
  parent: number;
}

interface IDictStatisticResponse {
  ok: boolean;
  message: string;
  data: IDict[];
}

const DictListWidget = () => {
  const [data, setData] = useState<IDict[]>();

  useEffect(() => {
    get<IDictStatisticResponse>("/v2/dict-statistic").then((json) => {
      if (json.ok) {
        setData(json.data);
      }
    });
  }, []);
  return (
    <ProCard
      tabs={{
        onChange: (key) => {
          console.log("key", key);
        },
        items: data?.map((item, id) => {
          return {
            key: item.key,
            style: { width: "100%" },
            label: (
              <StatisticCard
                colSpan={6}
                title={item.title}
                statistic={{
                  value: item.count,
                  description: (
                    <>
                      <Statistic title="单词表" value={item.vocabulary} />
                      <Statistic title="词干" value={item.parent} />
                    </>
                  ),
                }}
                style={{
                  borderInlineEnd:
                    id < data.length - 1 ? "1px solid #f0f0f0" : undefined,
                }}
              />
            ),
            children: <UserDictList view={"all"} dictName={item.key} />,
          };
        }),
      }}
    />
  );
};

export default DictListWidget;

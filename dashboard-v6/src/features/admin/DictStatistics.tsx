import { ProCard, StatisticCard } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import UserDictTable from "../../components/dict/UserDictTable";
import { useDictStatistic } from "../../hooks/useAdmin";

/** 各词典的统计卡（作为标签页头）+ 选中词典的词条表。 */
const DictStatistics = () => {
  const intl = useIntl();
  const data = useDictStatistic();

  return (
    <ProCard
      tabs={{
        items: data?.map((item, index) => ({
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
                    <StatisticCard.Statistic
                      title={intl.formatMessage({ id: "admin.dict.vocabulary" })}
                      value={item.vocabulary}
                    />
                    <StatisticCard.Statistic
                      title={intl.formatMessage({ id: "admin.dict.parent" })}
                      value={item.parent}
                    />
                  </>
                ),
              }}
              style={{
                borderInlineEnd: index < data.length - 1 ? "1px solid rgba(127,127,127,.15)" : undefined,
              }}
            />
          ),
          children: <UserDictTable view="all" dictName={item.key} />,
        })),
      }}
    />
  );
};

export default DictStatistics;

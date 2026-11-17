import type { NissayaRelation, NissayaRelationSide } from "../../api/nissaya";
import { fullUrl } from "../../utils";
import { Button, Space, Table, Tag, Typography } from "antd";
import lodash from "lodash";

import { ArrowRightOutlined } from "@ant-design/icons";
import { useIntl } from "react-intl";
import GrammarLookup from "../dict/GrammarLookup";
import Marked from "../general/Marked";

const { Link } = Typography;

const caseTags = [
  { case: "fpp", tags: ["verb", "derivative", "passive-verb"] },
  { case: "ger", tags: ["verb"] },
  { case: "inf", tags: ["verb"] },
  { case: "grd", tags: ["verb"] },
  { case: "pp", tags: ["verb", "participle"] },
  { case: "prp", tags: ["verb", "participle"] },
  { case: "v", tags: ["verb"] },
  { case: "v:ind", tags: ["verb"] },
  { case: "vdn", tags: ["verb", "participle"] },
];
interface ITags {
  tag: string;
  count: number;
}
const getCaseTags = (input: string[]): ITags[] => {
  const tagsMap = new Map<string, number>();
  input.forEach((value: string) => {
    const found = caseTags.find((value1) => value1.case === value);
    if (found !== undefined) {
      found.tags.forEach((value3) => {
        const count = tagsMap.get(value3);
        if (typeof count === "undefined") {
          tagsMap.set(value3, 1);
        } else {
          tagsMap.set(value3, count + 1);
        }
      });
    }
  });
  const tags: ITags[] = [];
  tagsMap.forEach((value, key) => {
    tags.push({ tag: key, count: value });
  });
  tags.sort((a, b) => b.count - a.count);
  return tags;
};

const randomString = () =>
  lodash.times(20, () => lodash.random(35).toString(36)).join("");

/** 关系的一端（来源 / 目标）：格位（带本地译名）与拼写，取自 v3 /nissaya-cards */
type IRelationNode = NissayaRelationSide;
interface DataType {
  key: string;
  relation: string;
  localRelation?: string;
  tags?: ITags[];
  to?: IRelationNode | null;
  from?: IRelationNode | null;
  category?: NissayaRelation["category"];
  translation?: string;
  isChildren?: boolean;
  children?: DataType[];
}
export type INissayaRelation = NissayaRelation;
interface IWidget {
  data?: INissayaRelation[];
}
const NissayaCardTableWidget = ({ data }: IWidget) => {
  const intl = useIntl();
  let tableData: DataType[] = [];

  if (typeof data === "undefined") {
    tableData = [];
  } else {
    console.log("data", data);
    const category: string[] = [];
    const newData: DataType[] = [];
    data.forEach((item) => {
      if (item.category && item.category.name) {
        const key = `${item.from?.spell}-${item.from?.cases
          ?.map((item) => item.label ?? item.case)
          .join()}-${item.relation}-${item.category}`;
        if (!category.includes(key)) {
          category.push(key);
          console.log("category", category);
          //处理children
          const children = data
            .filter(
              (value) =>
                `${value.from?.spell}-${value.from?.cases
                  ?.map((item) => item.label ?? item.case)
                  .join()}-${value.relation}-${value.category}` === key,
            )
            .map((item) => {
              return {
                key: randomString(),
                relation: item.relation ?? "",
                localRelation: item.relation_label ?? undefined,
                from: item.from,
                to: item.to,
                category: item.category,
                translation: item.local_endings?.join(";"),
                isChildren: true,
              };
            });
          console.log("children", children);
          const caseList: string[] = [];
          children.forEach((value) => {
            value.to?.cases?.forEach((value1) => {
              caseList.push(value1.case ?? "");
            });
          });
          const tags = getCaseTags(caseList);
          newData.push({
            key: randomString(),
            relation: item.relation ?? "",
            localRelation: item.relation_label ?? undefined,
            from: item.from,
            to: item.to,
            tags: tags,
            category: item.category,
            translation: item.local_endings?.join(";"),
            children: children.length > 1 ? [...children] : undefined,
          });
        }
      } else {
        newData.push({
          key: randomString(),
          relation: item.relation ?? "",
          localRelation: item.relation_label ?? undefined,
          from: item.from,
          to: item.to,
          category: item.category,
          translation: item.local_endings?.join(";"),
        });
      }
    });
    console.log("newData", newData);
    tableData = newData;
  }

  return (
    <Table
      size="small"
      columns={[
        {
          title: "本词特征",
          dataIndex: "from",
          key: "from",
          width: "10%",
          render: (_value, record) => {
            return (
              <Space>
                {record.from?.cases?.map((item, id) => {
                  return (
                    <GrammarLookup key={id} word={item.case}>
                      <Link>
                        <Tag>{item.label ?? item.case}</Tag>
                      </Link>
                    </GrammarLookup>
                  );
                })}
                {record.from?.spell}
              </Space>
            );
          },
        },
        {
          title: "关系",
          dataIndex: "relation",
          key: "relation",
          width: "30%",
          render: (_value, record) => {
            return (
              <Space orientation="vertical">
                <GrammarLookup word={record.relation}>
                  <Link>{record.relation}</Link>
                </GrammarLookup>
                <div>{record.localRelation}</div>
              </Space>
            );
          },
        },
        {
          title: "目标词特征",
          dataIndex: "to",
          key: "to",
          width: "20%",
          render: (_value, record) => {
            if (record.isChildren) {
              return (
                <Space>
                  <ArrowRightOutlined />
                  {record.to?.cases?.map((item, id) => {
                    return (
                      <Button
                        key={id}
                        type="link"
                        size="small"
                        onClick={() =>
                          window.open(
                            fullUrl(`/term/list/${item.case}`),
                            "_blank",
                          )
                        }
                      >
                        <Tag key={id}>{item.label ?? item.case}</Tag>
                      </Button>
                    );
                  })}
                  {record.to?.spell}
                </Space>
              );
            } else {
              return (
                <Space>
                  <ArrowRightOutlined />
                  {record.tags?.map((item, id) => {
                    return (
                      <Tag key={id}>
                        {intl.formatMessage({
                          id: `dict.case.category.${item.tag}`,
                        })}
                      </Tag>
                    );
                  })}
                </Space>
              );
            }
          },
        },
        {
          title: "语法点",
          dataIndex: "to",
          key: "grammar",
          width: "20%",
          render: (_value, record) => {
            if (!record.isChildren) {
              return (
                <GrammarLookup word={record.category?.name}>
                  <Link>{record.category?.meaning}</Link>
                </GrammarLookup>
              );
            }
          },
        },
        {
          title: "含义",
          dataIndex: "address",
          width: "40%",
          key: "address",
          render: (_value, record) => {
            if (record.isChildren) {
              return undefined;
            } else {
              return (
                <div>
                  <Marked text={record.category?.note} />
                  <div>{record.translation}</div>
                </div>
              );
            }
          },
        },
      ]}
      dataSource={tableData}
    />
  );
};

export default NissayaCardTableWidget;

import { Button, Space, Table, Tag, Typography } from "antd";
import lodash from "lodash";
import { useEffect, useState } from "react";
import { ArrowRightOutlined } from "@ant-design/icons";
import Marked from "./Marked";
import GrammarLookup from "../dict/GrammarLookup";
import { useIntl } from "react-intl";

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
  let tagsMap = new Map<string, number>();
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
  let tags: ITags[] = [];
  tagsMap.forEach((value, key, map) => {
    tags.push({ tag: key, count: value });
  });
  tags.sort((a, b) => b.count - a.count);
  return tags;
};

const randomString = () =>
  lodash.times(20, () => lodash.random(35).toString(36)).join("");

interface ICaseItem {
  label: string;
  case: string;
  link: string;
}
interface IRelationNode {
  case?: ICaseItem[];
  spell?: string;
}
interface DataType {
  key: string;
  relation: string;
  localRelation?: string;
  tags?: ITags[];
  to?: IRelationNode;
  from?: IRelationNode;
  category?: { name: string; note: string; meaning: string };
  translation?: string;
  isChildren?: boolean;
  children?: DataType[];
}
export interface INissayaRelation {
  from?: IRelationNode;
  to?: IRelationNode;
  category?: { name: string; note: string; meaning: string };
  local_ending: string;
  relation: string;
  local_relation?: string;
  relation_link: string;
}
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
    let category: string[] = [];
    let newData: DataType[] = [];
    data.forEach((item, index) => {
      if (item.category && item.category.name) {
        const key = `${item.from?.spell}-${item.from?.case
          ?.map((item) => item.label)
          .join()}-${item.relation}-${item.category}`;
        if (!category.includes(key)) {
          category.push(key);
          console.log("category", category);
          //处理children
          const children = data
            .filter(
              (value) =>
                `${value.from?.spell}-${value.from?.case
                  ?.map((item) => item.label)
                  .join()}-${value.relation}-${value.category}` === key
            )
            .map((item, index) => {
              return {
                key: randomString(),
                relation: item.relation,
                localRelation: item.local_relation,
                from: item.from,
                to: item.to,
                category: item.category,
                translation: item.local_ending,
                isChildren: true,
              };
            });
          console.log("children", children);
          let caseList: string[] = [];
          children.forEach((value) => {
            value.to?.case?.forEach((value1) => {
              caseList.push(value1.case);
            });
          });
          const tags = getCaseTags(caseList);
          newData.push({
            key: randomString(),
            relation: item.relation,
            localRelation: item.local_relation,
            from: item.from,
            to: item.to,
            tags: tags,
            category: item.category,
            translation: item.local_ending,
            children: children.length > 1 ? [...children] : undefined,
          });
        }
      } else {
        newData.push({
          key: randomString(),
          relation: item.relation,
          localRelation: item.local_relation,
          from: item.from,
          to: item.to,
          category: item.category,
          translation: item.local_ending,
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
          render: (value, record, index) => {
            return (
              <Space>
                {record.from?.case?.map((item, id) => {
                  return (
                    <GrammarLookup key={id} word={item.case}>
                      <Link>
                        <Tag>{item.label}</Tag>
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
          render: (value, record, index) => {
            return (
              <Space direction="vertical">
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
          render: (value, record, index) => {
            if (record.isChildren) {
              return (
                <Space>
                  <ArrowRightOutlined />
                  {record.to?.case?.map((item, id) => {
                    return (
                      <Button
                        key={id}
                        type="link"
                        size="small"
                        onClick={() => window.open(item.link, "_blank")}
                      >
                        <Tag key={id}>{item.label}</Tag>
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
          render: (value, record, index) => {
            if (!record.isChildren) {
              return record.category?.meaning;
            }
          },
        },
        {
          title: "含义",
          dataIndex: "address",
          width: "40%",
          key: "address",
          render: (value, record, index) => {
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

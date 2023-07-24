import { recordKeyToString } from "@ant-design/pro-components";
import { Button, Space, Table, Tag } from "antd";
import { useEffect, useState } from "react";

interface ICaseItem {
  label: string;
  link: string;
}
interface IRelationNode {
  case?: ICaseItem[];
  spell?: string;
}
interface DataType {
  key: React.ReactNode;
  relation: string;
  localRelation?: string;
  to?: IRelationNode;
  from?: IRelationNode;
  category?: { name: string; note: string; meaning: string };
  translation?: string;
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
  const [tableData, setTableData] = useState<DataType[]>();
  useEffect(() => {
    if (typeof data === "undefined") {
      setTableData(undefined);
      return;
    }
    let category: string[] = [];
    let newData: DataType[] = [];
    let id = 0;
    for (const item of data) {
      id++;
      if (item.category && item.category.name) {
        if (category.includes(item.category.name)) {
          continue;
        } else {
          category.push(item.category.name);
          //处理children
          const children = data
            .filter((value) => value.category?.name === item.category?.name)
            .map((item, index) => {
              return {
                key: `c_${index}`,
                relation: item.relation,
                from: item.from,
                to: item.to,
                category: item.category,
                translation: item.local_ending,
              };
            });
          newData.push({
            key: id,
            relation: item.relation,
            to: item.to,
            from: item.from,
            category: item.category,
            translation: item.local_ending,
            children: children,
          });
        }
      } else {
        newData.push({
          key: id,
          relation: item.relation,
          from: item.to,
          to: item.to,
          category: item.category,
          translation: item.local_ending,
        });
      }
    }

    setTableData(newData);
  }, [data]);
  return (
    <Table
      columns={[
        {
          title: "本词",
          dataIndex: "from",
          key: "from",
          render: (value, record, index) => {
            return (
              <Space>
                {record.from?.case?.map((item, id) => {
                  return (
                    <Button
                      key={id}
                      type="link"
                      size="small"
                      onClick={() => window.open(item.link, "_blank")}
                    >
                      <Tag>{item.label}</Tag>
                    </Button>
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
          width: "12%",
          render: (value, record, index) => {
            return <>{record.relation}</>;
          },
        },
        {
          title: "目标词特征",
          dataIndex: "to",
          key: "to",
          render: (value, record, index) => {
            if (record.children) {
              return <>{record.category?.meaning}</>;
            } else {
              return (
                <Space>
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
            }
          },
        },
        {
          title: "含义",
          dataIndex: "address",
          width: "30%",
          key: "address",
          render: (value, record, index) => {
            if (record.children) {
              return <>{record.category?.note}</>;
            } else {
              return undefined;
            }
          },
        },
        {
          title: "翻译建议",
          dataIndex: "translation",
          width: "20%",
          key: "translation",
        },
      ]}
      dataSource={tableData}
    />
  );
};

export default NissayaCardTableWidget;

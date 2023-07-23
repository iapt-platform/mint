import { Button, Table, Tag } from "antd";
import { useEffect, useState } from "react";

interface ICaseItem {
  label: string;
  link: string;
}
interface DataType {
  key: React.ReactNode;
  case?: ICaseItem[];
  spell?: string;
  relation: string;
  localRelation?: string;
  to?: ICaseItem[];
  category?: { name: string; note: string; meaning: string };
  translation?: string;
  children?: DataType[];
}
export interface INissayaRelation {
  case?: ICaseItem[];
  spell?: string;
  to?: ICaseItem[];
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
                case: item.case,
                spell: item.spell,
                relation: item.relation,
                to: item.to,
                category: item.category,
                translation: item.local_ending,
              };
            });
          newData.push({
            key: id,
            case: item.case,
            spell: item.spell,
            relation: item.relation,
            to: item.to,
            category: item.category,
            translation: item.local_ending,
            children: children,
          });
        }
      } else {
        newData.push({
          key: id,
          case: item.case,
          spell: item.spell,
          relation: item.relation,
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
              <>
                {record.case?.map((item, id) => {
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
              </>
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
                <>
                  {record.to?.map((item, id) => {
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
                </>
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

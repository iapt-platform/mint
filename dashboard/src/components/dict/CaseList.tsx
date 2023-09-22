import { Button, List, Tag, Typography } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { ICaseListResponse } from "../api/Dict";
const { Text } = Typography;

export interface ICaseListData {
  word: string;
  count: number;
  bold: number;
}
interface IWidget {
  word?: string;
  lines?: number;
}
const CaseListWidget = ({ word, lines }: IWidget) => {
  const [caseData, setCaseData] = useState<ICaseListData[]>();
  const [first, setFirst] = useState<string>();
  const [count, setCount] = useState<number>();
  const [showAll, setShowAll] = useState(lines ? false : true);
  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    get<ICaseListResponse>(`/v2/case/${word}`).then((json) => {
      console.log("case", json);
      if (json.ok && json.data.rows.length > 0) {
        const first = json.data.rows.sort((a, b) => b.count - a.count)[0];
        setCaseData(first.case.sort((a, b) => b.count - a.count));
        setCount(first.count);
        setFirst(first.word);
      }
    });
  }, [word]);
  return (
    <div style={{ padding: 4 }}>
      <List
        header={`${first}：${count}`}
        footer={
          lines ? (
            <Button type="link" onClick={() => setShowAll(!showAll)}>
              {showAll ? "折叠" : "展开"}
            </Button>
          ) : undefined
        }
        size="small"
        dataSource={showAll ? caseData : caseData?.slice(0, lines)}
        renderItem={(item) => (
          <List.Item>
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                width: "100%",
              }}
            >
              <Text strong={item.bold > 0 ? true : false}>{item.word}</Text>
              <Tag>{item.count}</Tag>
            </div>
          </List.Item>
        )}
      />
    </div>
  );
};

export default CaseListWidget;

import { List, Tag, Typography } from "antd";
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
}
const Widget = ({ word }: IWidget) => {
  const [caseData, setCaseData] = useState<ICaseListData[]>();
  const [count, setCount] = useState<number>();
  useEffect(() => {
    get<ICaseListResponse>(`/v2/case/${word}`).then((json) => {
      console.log("case", json);
      if (json.ok) {
        setCaseData(json.data.rows.sort((a, b) => b.count - a.count));
        setCount(json.data.count);
      }
    });
  }, [word]);
  return (
    <div style={{ padding: 4 }}>
      <List
        header={`单词数：${count}`}
        size="small"
        dataSource={caseData}
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

export default Widget;

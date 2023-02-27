import { List, Select, Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import {
  IApiResponseDictList,
  IDictFirstMeaningResponse,
  IFirstMeaning,
} from "../api/Dict";

const { Text } = Typography;

interface IOptions {
  value: string;
  label: string;
}
interface IWidget {
  word?: string;
  add?: string;
  split?: string;
}
const Widget = ({ word, add, split }: IWidget) => {
  const [compound, setCompound] = useState<IOptions[]>([]);
  const [factors, setFactors] = useState<IOptions[]>([]);
  const [meaningData, setMeaningData] = useState<IFirstMeaning[]>();
  const [currValue, setCurrValue] = useState<string>();
  const onSelectChange = (value: string) => {
    console.log("selected", value);
    get<IDictFirstMeaningResponse>(
      `/v2/dict-meaning?lang=zh-Hans&word=` + value.replaceAll("+", "-")
    ).then((json) => {
      if (json.ok) {
        setMeaningData(json.data);
      }
    });
  };
  useEffect(() => {
    if (typeof add === "undefined") {
      setFactors(compound);
    } else {
      setFactors([{ value: add, label: add }, ...compound]);
      setCurrValue(add);
      onSelectChange(add);
    }
  }, [add, compound]);
  useEffect(() => {
    get<IApiResponseDictList>(`/v2/userdict?view=compound&word=${word}`).then(
      (json) => {
        if (json.ok) {
          const data = json.data.rows.map((item) => {
            return { value: item.factors, label: item.factors };
          });
          setCompound(data);
        }
      }
    );
  }, [word]);
  return (
    <div>
      <Select
        value={currValue}
        style={{ width: "100%" }}
        onChange={onSelectChange}
        options={factors}
      />
      <List
        size="small"
        dataSource={meaningData}
        renderItem={(item) => (
          <List.Item>
            <div>
              <Text strong>{item.word}</Text>{" "}
              <Text type="secondary">{item.meaning}</Text>
            </div>
          </List.Item>
        )}
      />
    </div>
  );
};

export default Widget;

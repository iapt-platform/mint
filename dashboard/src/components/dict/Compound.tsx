import { List, Select, Typography } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import {
  IApiResponseDictList,
  IDictFirstMeaningResponse,
  IFirstMeaning,
} from "../api/Dict";

const { Text, Link } = Typography;

interface IOptions {
  value: string;
  label: string;
}
interface IWidget {
  word?: string;
  add?: string;
  split?: string;
  onSearch?: Function;
}
const CompoundWidget = ({ word, add, split, onSearch }: IWidget) => {
  const [compound, setCompound] = useState<IOptions[]>([]);
  const [factors, setFactors] = useState<IOptions[]>([]);
  const [meaningData, setMeaningData] = useState<IFirstMeaning[]>();
  const [currValue, setCurrValue] = useState<string>();
  const onSelectChange = (value?: string) => {
    console.log("selected", value);
    if (typeof value === "undefined") {
      setMeaningData(undefined);
    } else {
      get<IDictFirstMeaningResponse>(
        `/v2/dict-meaning?lang=zh-Hans&word=` + value.replaceAll("+", "-")
      ).then((json) => {
        if (json.ok) {
          setMeaningData(json.data);
        }
      });
    }
  };
  useEffect(() => {
    console.log("compound changed", add);
  }, [add]);
  useEffect(() => {
    console.log("compound changed", add, compound);
    if (typeof add === "undefined") {
      setFactors(compound);
      const value = compound.length > 0 ? compound[0].value : undefined;
      setCurrValue(value);
      onSelectChange(value);
    } else {
      setFactors([{ value: add, label: add }, ...compound]);
      setCurrValue(add);
      onSelectChange(add);
    }
  }, [add, compound]);
  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
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
    <div
      style={{
        width: "100%",
        maxWidth: 560,
        marginLeft: "auto",
        marginRight: "auto",
      }}
    >
      <Select
        value={currValue}
        style={{ width: "100%" }}
        onChange={onSelectChange}
        options={factors}
      />
      {meaningData ? (
        <List
          size="small"
          dataSource={meaningData}
          renderItem={(item) => (
            <List.Item>
              <div>
                <Link
                  strong
                  onClick={() => {
                    if (typeof onSearch !== "undefined") {
                      onSearch(item.word, true);
                    }
                  }}
                >
                  {item.word}
                </Link>{" "}
                <Text type="secondary">{item.meaning}</Text>
              </div>
            </List.Item>
          )}
        />
      ) : undefined}
    </div>
  );
};

export default CompoundWidget;

import { List, Select, Typography } from "antd";
import { useEffect, useState } from "react";
import { TeamOutlined, RobotOutlined } from "@ant-design/icons";

import { get } from "../../request";
import {
  IApiResponseDictList,
  IDictFirstMeaningResponse,
  IFirstMeaning,
} from "../api/Dict";

const { Text, Link } = Typography;

interface IFactorInfo {
  factors: string;
  type: string;
  confidence: number;
}
interface IOptions {
  value: string;
  label: React.ReactNode;
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
    setCurrValue(value);
    if (typeof value === "undefined") {
      setMeaningData(undefined);
    } else {
      const url =
        `/v2/dict-meaning?lang=zh-Hans&word=` + value.replaceAll("+", "-");
      console.info("dict compound url", url);
      get<IDictFirstMeaningResponse>(url).then((json) => {
        if (json.ok) {
          setMeaningData(json.data);
        }
      });
    }
  };

  useEffect(() => {
    console.debug("compound changed", add, compound);
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
    setMeaningData([]);
    setFactors([]);
    if (typeof word === "undefined") {
      return;
    }
    const url = `/v2/userdict?view=word&word=${word}`;
    console.info("dict compound url", url);
    get<IApiResponseDictList>(url).then((json) => {
      if (json.ok) {
        let factors = new Map<string, IFactorInfo>();
        json.data.rows
          .filter((value) => typeof value.factors === "string")
          .forEach((value) => {
            let type = "";
            if (value.source?.includes("_USER")) {
              type = "user";
            }
            if (value.type === ".cp.") {
              type = "robot";
            }
            if (value.factors) {
              factors.set(value.factors, {
                factors: value.factors,
                type: type,
                confidence: value.confidence,
              });
            }
          });
        let arrFactors: IFactorInfo[] = [];
        factors.forEach((value, key, map) => {
          arrFactors.push(value);
        });
        arrFactors.sort((a, b) => b.confidence - a.confidence);
        setCompound(
          arrFactors.map((item, id) => {
            return {
              value: item.factors,
              label: (
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                  }}
                >
                  {item.factors}
                  {item.type === "user" ? (
                    <TeamOutlined />
                  ) : item.type === "robot" ? (
                    <RobotOutlined />
                  ) : (
                    <></>
                  )}
                </div>
              ),
            };
          })
        );
      }
    });
  }, [word]);
  return (
    <div
      className="dict_compound_div"
      style={{
        width: "100%",
        maxWidth: 560,
        marginLeft: "auto",
        marginRight: "auto",
      }}
    >
      <Select
        getPopupContainer={(node: HTMLElement) =>
          document.getElementsByClassName("dict_compound_div")[0] as HTMLElement
        }
        value={currValue}
        style={{ width: "100%" }}
        onChange={onSelectChange}
        options={factors}
      />
      {meaningData && meaningData.length > 0 ? (
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

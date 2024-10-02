import { Badge, Button, Card, Checkbox, Select, Space, Typography } from "antd";
import { DownOutlined, UpOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { ICaseItem, ICaseListResponse } from "../api/Dict";
import { CheckboxValueType } from "antd/lib/checkbox/Group";
import { CheckboxChangeEvent } from "antd/es/checkbox";
const { Text } = Typography;

export interface ICaseListData {
  word: string;
  count: number;
  bold: number;
}
interface IWidget {
  word?: string;
  lines?: number;
  onChange?: Function;
}
const CaseListWidget = ({ word, lines, onChange }: IWidget) => {
  const [caseData, setCaseData] = useState<ICaseListData[]>();
  const [showAll, setShowAll] = useState(lines ? false : true);
  const [words, setWords] = useState<ICaseItem[]>();
  const [currWord, setCurrWord] = useState<string>();
  const [checkedList, setCheckedList] = useState<CheckboxValueType[]>([]);

  useEffect(() => {
    setCaseData(
      words
        ?.find((value) => value.word === currWord)
        ?.case.sort((a, b) => b.count - a.count)
    );
  }, [currWord, words]);

  useEffect(() => {
    if (typeof onChange !== "undefined" && checkedList.length > 0) {
      onChange(checkedList);
    }
  }, [checkedList]);

  useEffect(() => {
    if (caseData) {
      setCheckedList(caseData?.map((item) => item.word));
    } else {
      setCheckedList([]);
    }
  }, [caseData]);

  useEffect(() => {
    /**
     * 搜索变格
     * 如果 keyWord 包涵空格 不搜索
     */
    if (typeof word === "undefined") {
      return;
    }
    if (word?.trim().includes(" ")) {
      setWords([]);
      setCurrWord(undefined);
      return;
    }
    get<ICaseListResponse>(`/v2/case/${word}`).then((json) => {
      console.log("case", json);
      if (json.ok && json.data.rows.length > 0) {
        setWords(json.data.rows);
        const first = json.data.rows.sort((a, b) => b.count - a.count)[0];
        setCurrWord(first.word);
      }
    });
  }, [word]);

  let checkAll = true;
  let indeterminate = false;
  if (caseData && checkedList) {
    checkAll = caseData?.length === checkedList?.length;
    indeterminate =
      checkedList.length > 0 && checkedList.length < caseData.length;
  }

  const onWordChange = (list: CheckboxValueType[]) => {
    setCheckedList(list);
  };

  const onCheckAllChange = (e: CheckboxChangeEvent) => {
    if (caseData) {
      setCheckedList(
        e.target.checked ? caseData?.map((item) => item.word) : []
      );
    } else {
      setCheckedList([]);
    }
  };

  const showWords = showAll ? caseData : caseData?.slice(0, lines);
  return (
    <div style={{ padding: 4 }}>
      {currWord ? (
        <Card
          size="small"
          extra={
            lines ? (
              <Button type="link" onClick={() => setShowAll(!showAll)}>
                {showAll ? (
                  <Space>
                    {"折叠"}
                    <UpOutlined />
                  </Space>
                ) : (
                  <Space>
                    {"展开"}
                    <DownOutlined />
                  </Space>
                )}
              </Button>
            ) : (
              <></>
            )
          }
          title={
            <Select
              value={currWord}
              bordered={false}
              onChange={(value: string) => {
                setCurrWord(value);
              }}
              options={words?.map((item, id) => {
                return {
                  label: (
                    <Space>
                      {item.word}
                      <Badge
                        count={item.count}
                        color={"lime"}
                        status="default"
                        size="small"
                      />
                    </Space>
                  ),
                  value: item.word,
                };
              })}
            />
          }
        >
          <Checkbox
            indeterminate={indeterminate}
            onChange={onCheckAllChange}
            checked={checkAll}
          >
            Check all
          </Checkbox>
          <Checkbox.Group
            style={{ display: "grid" }}
            options={showWords?.map((item, id) => {
              return {
                label: (
                  <Space>
                    <Text strong={item.bold > 0 ? true : false}>
                      {item.word}
                    </Text>
                    <Badge
                      size="small"
                      count={item.count}
                      overflowCount={9999}
                      status="default"
                    />
                  </Space>
                ),
                value: item.word,
              };
            })}
            value={checkedList}
            onChange={onWordChange}
          />
        </Card>
      ) : (
        <Text>多词搜索没有变格词表</Text>
      )}
    </div>
  );
};

export default CaseListWidget;

import { useState, useEffect } from "react";
import { Col, List, Row, Typography } from "antd";

import { get } from "../../request";
import {
  ITermDataResponse,
  ITermListResponse,
  ITermResponse,
} from "../api/Term";
import TermItem from "./TermItem";
import { ITerm } from "./TermEdit";
const { Title } = Typography;

interface IWidget {
  word?: string;
  wordId?: string;
  compact?: boolean;
  onIdChange?: Function;
}
const TermSearchWidget = ({
  word,
  wordId,
  compact = false,
  onIdChange,
}: IWidget) => {
  const [tableData, setTableData] = useState<ITermDataResponse[]>();

  const loadById = (id: string) => {
    const url = `/v2/terms/${id}`;
    console.info("term url", url);
    get<ITermResponse>(url)
      .then((json) => {
        setTableData([json.data]);
      })
      .catch((error) => {
        console.error(error);
      });
  };
  useEffect(() => {
    if (typeof word === "undefined" && typeof wordId === "undefined") {
      return;
    }
    if (word) {
      const url = `/v2/terms?view=word&word=${word}`;
      get<ITermListResponse>(url)
        .then((json) => {
          setTableData(json.data.rows);
        })
        .catch((error) => {
          console.error(error);
        });
    } else if (wordId) {
      loadById(wordId);
    }
  }, [word, wordId]);

  return (
    <>
      <Row>
        <Col flex="200px">{compact ? <></> : <></>}</Col>
        <Col flex="760px">
          <Title>{word}</Title>
          <List
            itemLayout="vertical"
            size="large"
            dataSource={tableData}
            renderItem={(item) => (
              <List.Item>
                <TermItem
                  data={item}
                  onTermClick={(value: ITerm) => {
                    console.debug("on term click", value);
                    if (typeof onIdChange === "undefined") {
                      if (value.id) {
                        loadById(value.id);
                      }
                    } else {
                      onIdChange(value.id);
                    }
                  }}
                />
              </List.Item>
            )}
          />
        </Col>
        <Col flex="200px"></Col>
      </Row>
    </>
  );
};

export default TermSearchWidget;

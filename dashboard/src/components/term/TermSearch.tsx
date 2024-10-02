import { useState, useEffect } from "react";
import { Col, List, Row, Skeleton, Typography } from "antd";

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
  const [loading, setLoading] = useState(false);

  const loadById = (id: string) => {
    const url = `/v2/terms/${id}`;
    console.info("term url", url);
    setLoading(true);
    get<ITermResponse>(url)
      .then((json) => {
        setTableData([json.data]);
      })
      .finally(() => setLoading(false))
      .catch((error) => {
        console.error(error);
      });
  };
  useEffect(() => {
    if (typeof word === "undefined" && typeof wordId === "undefined") {
      return;
    }
    if (word && word.length > 0) {
      const url = `/v2/terms?view=word&word=${word}`;
      console.info("term url", url);
      setLoading(true);
      get<ITermListResponse>(url)
        .then((json) => {
          setTableData(json.data.rows);
        })
        .finally(() => setLoading(false))
        .catch((error) => {
          console.error(error);
        });
    } else if (wordId && wordId.length > 0) {
      loadById(wordId);
    }
  }, [word, wordId]);

  return (
    <>
      <Row>
        <Col flex="200px">{compact ? <></> : <></>}</Col>
        <Col flex="760px">
          <Title level={4}>{word}</Title>
          {loading ? (
            <Skeleton active />
          ) : (
            <div>
              {tableData?.map((item, id) => {
                return (
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
                );
              })}
            </div>
          )}
        </Col>
        <Col flex="200px"></Col>
      </Row>
    </>
  );
};

export default TermSearchWidget;

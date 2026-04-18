import { useEffect, useState } from "react";
import { Col, Row, Skeleton, Typography } from "antd";

import { get } from "../../request";
import type {
  ITerm,
  ITermDataResponse,
  ITermListResponse,
  ITermResponse,
} from "../../api/Term";
import TermItem from "./TermItem";

const { Title } = Typography;

interface IWidget {
  word?: string;
  wordId?: string;
  compact?: boolean;
  onIdChange?: (id: string) => void;
}

const TermSearchWidget = ({
  word,
  wordId,
  compact = false,
  onIdChange,
}: IWidget) => {
  const [tableData, setTableData] = useState<ITermDataResponse[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!word && !wordId) return;

    let cancelled = false;

    const fetchData = async () => {
      setLoading(true);

      try {
        if (word && word.length > 0) {
          const url = `/api/v2/terms?view=word&word=${word}`;
          console.info("term url", url);

          const json = await get<ITermListResponse>(url);

          if (!cancelled) {
            setTableData(json.data.rows);
          }
        } else if (wordId && wordId.length > 0) {
          const url = `/api/v2/terms/${wordId}`;
          console.info("term url", url);

          const json = await get<ITermResponse>(url);

          if (!cancelled) {
            setTableData([json.data]);
          }
        }
      } catch (error) {
        console.error(error);
      } finally {
        if (!cancelled) setLoading(false);
      }
    };

    fetchData();

    return () => {
      cancelled = true;
    };
  }, [word, wordId]);

  const handleTermClick = (value: ITerm) => {
    if (!value.id) return;

    if (onIdChange) {
      onIdChange(value.id);
    } else {
      // 内部加载
      (async () => {
        setLoading(true);
        try {
          const json = await get<ITermResponse>(`/api/v2/terms/${value.id}`);
          setTableData([json.data]);
        } finally {
          setLoading(false);
        }
      })();
    }
  };

  return (
    <Row>
      <Col flex="200px">{!compact && null}</Col>

      <Col flex="760px">
        <Title level={4}>{word}</Title>

        {loading ? (
          <Skeleton active />
        ) : (
          <div>
            {tableData.map((item) => (
              <TermItem
                key={item.id}
                data={item}
                onTermClick={handleTermClick}
              />
            ))}
          </div>
        )}
      </Col>

      <Col flex="200px" />
    </Row>
  );
};

export default TermSearchWidget;

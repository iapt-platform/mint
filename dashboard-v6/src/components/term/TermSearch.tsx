import { useEffect, useState } from "react";
import { Col, Row, Skeleton, Typography } from "antd";

import { fetchTerm, lookupTerms, type ITerm, type Term } from "../../api/Term";
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
  const [tableData, setTableData] = useState<Term[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!word && !wordId) return;

    let cancelled = false;

    const fetchData = async () => {
      setLoading(true);

      try {
        if (word && word.length > 0) {
          const json = await lookupTerms({
            word,
            with_body: true,
            per_page: 100,
          });
          if (!cancelled) {
            setTableData(json.data ?? []);
          }
        } else if (wordId && wordId.length > 0) {
          const term = await fetchTerm(wordId);
          if (!cancelled && term) {
            setTableData([term]);
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
          const term = await fetchTerm(value.id ?? "");
          if (term) {
            setTableData([term]);
          }
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

import { Link } from "react-router-dom";
import { Badge, Card, List, message, Modal, Skeleton } from "antd";

import { get } from "../../request";
import { useEffect, useState } from "react";
import TocPath, { ITocPathNode } from "./TocPath";
import store from "../../store";
import { change } from "../../reducers/para-change";

interface ITag {
  id?: string;
  name: string;
  color?: string;
}
interface IRelatedParaData {
  book: number;
  para: number[];
  book_title_pali: string;
  book_title?: string;
  cs6_para: number;
  path?: ITocPathNode[];
  tags?: ITag[];
}
interface IRelatedParaResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IRelatedParaData[];
    count: number;
  };
}
interface IWidget {
  book?: number;
  para?: number;
  trigger?: JSX.Element;
  onSelect?: Function;
}
const RelatedParaWidget = ({ book, para, trigger, onSelect }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [tableData, setTableData] = useState<IRelatedParaData[]>([]);
  const [load, setLoad] = useState(true);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };
  useEffect(() => {
    if (typeof book === "number" && typeof para === "number" && isModalOpen) {
      setLoad(true);
      get<IRelatedParaResponse>(
        `/v2/related-paragraph?book=${book}&para=${para}`
      )
        .then((json) => {
          console.log("import", json);
          if (json.ok) {
            setTableData(json.data.rows);
          } else {
            message.error(json.message);
          }
        })
        .finally(() => setLoad(false));
    }
  }, [book, para, isModalOpen]);

  return (
    <>
      <span onClick={showModal}>{trigger ? trigger : "相关段落"}</span>
      <Modal
        title="根本和注疏"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        {load ? (
          <Skeleton paragraph={{ rows: 4 }} active />
        ) : (
          <List
            itemLayout="vertical"
            size="small"
            split={false}
            dataSource={tableData}
            renderItem={(item) => {
              const isPali = item.tags?.find((tag) => tag.name === "pāḷi");
              const isAttha = item.tags?.find(
                (tag) => tag.name === "aṭṭhakathā"
              );
              const isTika = item.tags?.find((tag) => tag.name === "ṭīkā");
              const firstPara = item.para.length > 0 ? item.para[0] : 0;
              return (
                <List.Item>
                  <Badge.Ribbon
                    text={
                      isPali
                        ? "pāḷi"
                        : isAttha
                        ? "aṭṭhakathā"
                        : isTika
                        ? "ṭīkā"
                        : undefined
                    }
                    color={
                      isPali
                        ? "volcano"
                        : isAttha
                        ? "green"
                        : isTika
                        ? "cyan"
                        : undefined
                    }
                  >
                    <Card
                      title={
                        <Link
                          to={`/article/para/${item.book}-${firstPara}?book=${item.book}&par=${item.para}`}
                          target="_blank"
                        >
                          {item.book_title_pali}
                        </Link>
                      }
                      size="small"
                    >
                      <TocPath
                        data={item.path}
                        onChange={(
                          node: ITocPathNode,
                          e: React.MouseEvent<
                            HTMLSpanElement | HTMLAnchorElement,
                            MouseEvent
                          >
                        ) => {
                          if (node.book && node.paragraph) {
                            const type = node.level
                              ? node.level < 8
                                ? "chapter"
                                : "para"
                              : "para";
                            store.dispatch(
                              change({
                                book: node.book,
                                para: node.paragraph,
                                type: type,
                              })
                            );
                          }
                        }}
                      />
                    </Card>
                  </Badge.Ribbon>
                </List.Item>
              );
            }}
          />
        )}
      </Modal>
    </>
  );
};

export default RelatedParaWidget;

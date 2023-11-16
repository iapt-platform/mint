import { useEffect, useState } from "react";
import { Button, message, Modal, Popover, Skeleton, Typography } from "antd";
import { EditOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { get as getLang } from "../../locales";

import NissayaCardTable, { INissayaRelation } from "./NissayaCardTable";
import { ITerm } from "../term/TermEdit";
import { Link } from "react-router-dom";

const { Paragraph, Title } = Typography;

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element | string;
}
export const NissayaCardPop = ({ text, trigger }: INissayaCardModal) => {
  return (
    <Popover
      style={{ width: 600 }}
      content={<NissayaCardWidget text={text} cache={true} />}
      placement="bottom"
    >
      <Typography.Link>{trigger}</Typography.Link>
    </Popover>
  );
};

export const NissayaCardModal = ({ text, trigger }: INissayaCardModal) => {
  const [isModalOpen, setIsModalOpen] = useState(false);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={800}
        title="缅文语尾"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        destroyOnClose
      >
        <NissayaCardWidget text={text} />
      </Modal>
    </>
  );
};

interface INissayaCardData {
  row: INissayaRelation[];
  ending: ITerm;
}
interface INissayaCardResponse {
  ok: boolean;
  message: string;
  data: INissayaCardData;
}

interface IWidget {
  text?: string;
  cache?: boolean;
}
const NissayaCardWidget = ({ text, cache = false }: IWidget) => {
  const [cardData, setCardData] = useState<INissayaRelation[]>();
  const [term, setTerm] = useState<ITerm>();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const uiLang = getLang();
    if (cache) {
      const value = sessionStorage.getItem(`nissaya-ending/${uiLang}/${text}`);
      if (value !== null) {
        const valueJson: INissayaCardData = JSON.parse(value);
        setCardData(valueJson.row);
        setTerm(valueJson.ending);
        setLoading(false);
        return;
      }
    }

    const url = `/v2/nissaya-card/${text}?lang=${uiLang}&content_type=json`;
    console.log("url", url);
    setLoading(true);
    get<INissayaCardResponse>(url)
      .then((json) => {
        if (json.ok) {
          setCardData(json.data.row);
          setTerm(json.data.ending);
          if (cache) {
            sessionStorage.setItem(
              `nissaya-ending/${uiLang}/${text}`,
              JSON.stringify(json.data)
            );
          }
        } else {
          message.error(json.message);
        }
      })
      .finally(() => setLoading(false))
      .catch((e: INissayaCardResponse) => message.error(e.message));
  }, [cache, text]);

  return loading ? (
    <Skeleton title={{ width: 200 }} paragraph={{ rows: 4 }} active />
  ) : (
    <div style={{ maxWidth: 750 }}>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Title level={4}>
          {term?.word}
          <Button type="link" icon={<EditOutlined />} />
        </Title>
        <Link to={`/nissaya/ending/${term?.word}`}>在新窗口打开</Link>
      </div>
      <Paragraph>{term?.meaning}</Paragraph>
      <Paragraph>{term?.note}</Paragraph>
      {cardData ? <NissayaCardTable data={cardData} /> : undefined}
    </div>
  );
};

export default NissayaCardWidget;

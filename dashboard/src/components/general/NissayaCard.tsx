import { useEffect, useState } from "react";
import { Button, message, Modal, Popover, Skeleton, Typography } from "antd";
import { EditOutlined, ReloadOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { get as getLang } from "../../locales";

import NissayaCardTable, { INissayaRelation } from "./NissayaCardTable";
import { ITerm } from "../term/TermEdit";
import { Link } from "react-router-dom";
import TermModal from "../term/TermModal";
import { ITermDataResponse } from "../api/Term";
import { useIntl } from "react-intl";
import MdView from "../template/MdView";

const { Paragraph, Title } = Typography;

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element | string;
}
export const NissayaCardPop = ({ text, trigger }: INissayaCardModal) => {
  return (
    <Popover
      style={{ width: 700 }}
      content={<NissayaCardWidget text={text} cache={true} hideEditButton />}
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
  hideEditButton?: boolean;
}
const NissayaCardWidget = ({
  text,
  cache = false,
  hideEditButton = false,
}: IWidget) => {
  const intl = useIntl();
  const [cardData, setCardData] = useState<INissayaRelation[]>();
  const [term, setTerm] = useState<ITerm>();
  const [loading, setLoading] = useState(false);

  useEffect(() => load(), [cache, text]);

  const load = () => {
    const uiLang = getLang();
    const key = `nissaya-ending/${uiLang}/${text}`;
    if (cache) {
      const value = sessionStorage.getItem(key);
      if (value !== null) {
        const valueJson: INissayaCardData = JSON.parse(value);
        setCardData(valueJson.row);
        setTerm(valueJson.ending);
        setLoading(false);
        return;
      }
    }

    const url = `/v2/nissaya-card/${text}?lang=${uiLang}&content_type=json`;
    console.debug("api request", url);
    setLoading(true);
    get<INissayaCardResponse>(url)
      .then((json) => {
        console.debug("api response", json);
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
  };

  const reload = () => {
    const uiLang = getLang();
    const key = `nissaya-ending/${uiLang}/${text}`;
    sessionStorage.removeItem(key);
    load();
  };
  return loading ? (
    <Skeleton title={{ width: 200 }} paragraph={{ rows: 4 }} active />
  ) : (
    <div style={{ maxWidth: 750 }}>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Title level={4}>
          {term?.word}
          {hideEditButton ? (
            <></>
          ) : (
            <TermModal
              id={term?.id}
              onUpdate={(value: ITermDataResponse) => {
                //onModalClose();
              }}
              onClose={() => {
                //onModalClose();
              }}
              trigger={<Button type="link" icon={<EditOutlined />} />}
            />
          )}
        </Title>
        <div>
          <Link to={`/nissaya/ending/${term?.word}`} target="_blank">
            {intl.formatMessage({
              id: "buttons.open.in.new.tab",
            })}
          </Link>
          <Button
            type="link"
            icon={<ReloadOutlined />}
            onClick={() => reload()}
          />
        </div>
      </div>
      <Paragraph>{term?.meaning}</Paragraph>
      <MdView html={term?.html} />
      {cardData ? <NissayaCardTable data={cardData} /> : <></>}
    </div>
  );
};

export default NissayaCardWidget;

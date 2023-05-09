import { useEffect, useState } from "react";
import { Modal, Popover, Typography } from "antd";

import { get } from "../../request";
import { get as getLang } from "../../locales";
import { IGuideResponse } from "../api/Guide";
import Marked from "../general/Marked";
const { Link } = Typography;

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element | string;
}
export const NissayaCardPop = ({ text, trigger }: INissayaCardModal) => {
  return (
    <Popover
      content={<NissayaCardWidget text={text} cache={true} />}
      placement="bottom"
    >
      <Link>{trigger}</Link>
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

interface IWidget {
  text?: string;
  cache?: boolean;
}
const NissayaCardWidget = ({ text, cache = false }: IWidget) => {
  const [guide, setGuide] = useState("Loading");

  useEffect(() => {
    const uiLang = getLang();
    if (cache) {
      const value = sessionStorage.getItem(`nissaya-ending/${uiLang}/${text}`);
      if (value !== null) {
        setGuide(value);
        return;
      }
    }

    const url = `/v2/nissaya-ending-card?lang=${uiLang}&ending=${text}`;
    get<IGuideResponse>(url).then((json) => {
      if (json.ok) {
        setGuide(json.data);
        if (cache) {
          sessionStorage.setItem(`nissaya-ending/${uiLang}/${text}`, json.data);
        }
      }
    });
  }, [cache, text]);

  return <Marked text={guide} />;
};

export default NissayaCardWidget;

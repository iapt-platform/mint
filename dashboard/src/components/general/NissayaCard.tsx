import { useEffect, useState } from "react";
import { Modal } from "antd";

import { get } from "../../request";
import { get as getLang } from "../../locales";
import { IGuideResponse } from "../api/Guide";
import Marked from "../general/Marked";

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element;
}
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
        <Widget text={text} />
      </Modal>
    </>
  );
};

interface IWidget {
  text?: string;
}
const Widget = ({ text }: IWidget) => {
  const [guide, setGuide] = useState("Loading");

  useEffect(() => {
    const uiLang = getLang();
    const url = `/v2/nissaya-ending-card?lang=${uiLang}&ending=${text}`;
    get<IGuideResponse>(url).then((json) => {
      if (json.ok) {
        setGuide(json.data);
      }
    });
  }, [text]);

  return <Marked text={guide} />;
};

export default Widget;

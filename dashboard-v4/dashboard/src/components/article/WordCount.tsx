import { Descriptions, Modal } from "antd";
import { LoadingOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { IArticleResponse } from "../api/Article";

interface IWidget {
  open?: boolean;
  articleId?: string;
  onClose?: Function;
}
const WordCount = ({ open = false, articleId, onClose }: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [wordAll, setWordAll] = useState(0);
  useEffect(() => {
    if (!articleId) {
      return;
    }
    const url = `/v2/article/${articleId}?format=text&origin=true`;
    console.info("url", url);
    setLoading(true);
    get<IArticleResponse>(url)
      .then((json) => {
        console.info("api response", json);
        if (json.ok) {
          if (json.data.html) {
            setWordAll(json.data.html.length);
          }
        }
      })
      .finally(() => setLoading(false));
  }, [articleId]);

  return (
    <Modal
      destroyOnClose={true}
      width={700}
      title={"字数统计"}
      open={open}
      footer={false}
      onOk={() => {
        if (typeof onClose !== "undefined") {
          onClose();
        }
      }}
      onCancel={() => {
        if (typeof onClose !== "undefined") {
          onClose();
        }
      }}
    >
      {loading ? (
        <LoadingOutlined />
      ) : (
        <Descriptions title="字数">
          <Descriptions.Item label="全部字符">{wordAll}</Descriptions.Item>
        </Descriptions>
      )}
    </Modal>
  );
};

export default WordCount;

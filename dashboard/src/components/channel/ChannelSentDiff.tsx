import { Button, Col, List, message, Row, Space, Typography } from "antd";
import { diffChars } from "diff";
import { useEffect, useState } from "react";
import { SwapRightOutlined } from "@ant-design/icons";

import { post } from "../../request";
import {
  ISentenceDiffData,
  ISentenceDiffRequest,
  ISentenceDiffResponse,
  ISentenceNewMultiResponse,
  ISentenceNewRequest,
} from "../api/Corpus";
import { IChannel } from "./Channel";

const { Text } = Typography;

interface IDiffData {
  id: string;
  srcContent?: string;
  destContent?: string | JSX.Element;
}
interface ISentenceData {
  book: number;
  paragraph: number;
  wordStart: number;
  wordEnd: number;
  content: string;
}

interface IWidget {
  srcChannel?: IChannel;
  destChannel?: IChannel;
  sentences?: string[];
  goPrev?: Function;
  onSubmit?: Function;
}
const Widget = ({
  srcChannel,
  destChannel,
  sentences,
  goPrev,
  onSubmit,
}: IWidget) => {
  const [srcApiData, setSrcApiData] = useState<ISentenceDiffData[]>([]);
  const [srcData, setSrcData] = useState<ISentenceData[]>([]);
  const [destData, setDestData] = useState<ISentenceData[]>([]);
  const [diffData, setDiffData] = useState<IDiffData[]>();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (sentences && srcChannel) {
      post<ISentenceDiffRequest, ISentenceDiffResponse>(`/v2/sent-in-channel`, {
        sentences: sentences,
        channel: srcChannel.id,
      }).then((json) => {
        if (json.ok) {
          console.log("src", srcChannel.id, json.data.rows);
          setSrcApiData(json.data.rows);
          const data = json.data.rows.map((item) => {
            return {
              book: item.book_id,
              paragraph: item.paragraph,
              wordStart: item.word_start,
              wordEnd: item.word_end,
              content: item.content,
            };
          });
          setSrcData(data);
        }
      });
    }
  }, [srcChannel, sentences]);

  useEffect(() => {
    if (sentences && destChannel) {
      post<ISentenceDiffRequest, ISentenceDiffResponse>(`/v2/sent-in-channel`, {
        sentences: sentences,
        channel: destChannel.id,
      }).then((json) => {
        if (json.ok) {
          console.log("dest", destChannel.id, json.data.rows);
          const data = json.data.rows.map((item) => {
            return {
              book: item.book_id,
              paragraph: item.paragraph,
              wordStart: item.word_start,
              wordEnd: item.word_end,
              content: item.content,
            };
          });
          setDestData(data);
        }
      });
    }
  }, [destChannel, sentences]);

  useEffect(() => {
    const diffList = sentences?.map((item) => {
      const id = item.split("-");
      const srcContent = srcData.find(
        (element) =>
          element.book === parseInt(id[0]) &&
          element.paragraph === parseInt(id[1]) &&
          element.wordStart === parseInt(id[2]) &&
          element.wordEnd === parseInt(id[3])
      );

      const destContent = destData.find(
        (element) =>
          element.book === parseInt(id[0]) &&
          element.paragraph === parseInt(id[1]) &&
          element.wordStart === parseInt(id[2]) &&
          element.wordEnd === parseInt(id[3])
      );
      const diff = diffChars(
        destContent ? destContent.content : "",
        srcContent ? srcContent.content : ""
      );
      const diffResult = diff.map((item) => {
        return (
          <Text
            type={
              item.added ? "success" : item.removed ? "danger" : "secondary"
            }
            delete={item.removed ? true : undefined}
          >
            {item.value}
          </Text>
        );
      });
      return {
        id: item,
        srcContent: srcContent?.content,
        destContent: <>{diffResult}</>,
      };
    });
    setDiffData(diffList);
  }, [destData, sentences, srcData]);

  return (
    <div>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Button
          onClick={() => {
            if (typeof goPrev !== "undefined") {
              goPrev();
            }
          }}
        >
          上一步
        </Button>
        <Space>
          {srcChannel?.name}
          <SwapRightOutlined />
          {destChannel?.name}
        </Space>
        <Button
          type="primary"
          loading={loading}
          onClick={() => {
            setLoading(true);
            post<ISentenceNewRequest, ISentenceNewMultiResponse>(
              `/v2/sentence`,
              {
                sentences: srcApiData,
                channel: destChannel?.id,
              }
            )
              .then((json) => {
                if (json.ok) {
                  if (typeof onSubmit !== "undefined") {
                    onSubmit();
                  }
                } else {
                  message.error(json.message);
                }
              })
              .catch((e) => {
                console.log(e);
              })
              .finally(() => {
                setLoading(false);
              });
          }}
        >
          开始复制
        </Button>
      </div>
      <List
        header={<div>Header</div>}
        footer={<div>Footer</div>}
        bordered
        dataSource={diffData}
        renderItem={(item) => (
          <List.Item>
            <Row style={{ width: "100%" }}>
              <Col span={12}>{item.srcContent}</Col>
              <Col span={12}>{item.destContent}</Col>
            </Row>
          </List.Item>
        )}
      />
    </div>
  );
};

export default Widget;

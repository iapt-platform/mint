import { Button, message, Select, Table, Tooltip, Typography } from "antd";
import { Change, diffChars } from "diff";
import { useEffect, useState } from "react";

import { post } from "../../request";
import {
  ISentenceDiffData,
  ISentenceDiffRequest,
  ISentenceDiffResponse,
  ISentenceListResponse,
  ISentenceNewRequest,
} from "../api/Corpus";
import { IChannel } from "./Channel";
import { ISentence, toISentence } from "../template/SentEdit";
import store from "../../store";
import { accept } from "../../reducers/accept-pr";

const { Text } = Typography;

interface IDataType {
  key: React.Key;
  sentId: string;
  pali?: string | null;
  srcContent?: string | null;
  destContent?: string | null;
}

interface IWidget {
  srcChannel?: IChannel;
  destChannel?: IChannel;
  sentences?: string[];
  important?: boolean;
  goPrev?: Function;
  onSubmit?: Function;
}
const ChannelSentDiffWidget = ({
  srcChannel,
  destChannel,
  sentences,
  important = false,
  goPrev,
  onSubmit,
}: IWidget) => {
  const [srcApiData, setSrcApiData] = useState<ISentenceDiffData[]>([]);
  const [diffData, setDiffData] = useState<IDataType[]>();
  const [loading, setLoading] = useState(false);
  const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>();
  const [newRowKeys, setNewRowKeys] = useState<React.Key[]>();
  const [emptyRowKeys, setEmptyRowKeys] = useState<React.Key[]>();

  useEffect(() => {
    if (sentences && srcChannel && destChannel) {
      post<ISentenceDiffRequest, ISentenceDiffResponse>(`/v2/sent-in-channel`, {
        sentences: sentences,
        channels: ["_System_Pali_VRI_", srcChannel.id, destChannel.id],
      }).then((json) => {
        if (json.ok) {
          const apiData = json.data.rows;
          setSrcApiData(apiData);
          let newRows: string[] = [];
          let emptyRows: string[] = [];
          const diffList: IDataType[] = sentences?.map((item, index) => {
            const id: string[] = item.split("-");
            const srcContent = apiData.find(
              (element) =>
                element.book_id === parseInt(id[0]) &&
                element.paragraph === parseInt(id[1]) &&
                element.word_start === parseInt(id[2]) &&
                element.word_end === parseInt(id[3]) &&
                element.channel_uid === srcChannel.id
            );

            const destContent = apiData.find(
              (element) =>
                element.book_id === parseInt(id[0]) &&
                element.paragraph === parseInt(id[1]) &&
                element.word_start === parseInt(id[2]) &&
                element.word_end === parseInt(id[3]) &&
                element.channel_uid === destChannel.id
            );
            if (srcContent && destContent) {
              const srcDate = new Date(srcContent.updated_at);
              const destDate = new Date(destContent.updated_at);
              if (srcDate > destDate) {
                newRows.push(item);
              }
            }
            if (
              typeof destContent === "undefined" ||
              destContent.content?.trim().length === 0
            ) {
              emptyRows.push(item);
            }
            const paliContent = apiData.find(
              (element) =>
                element.book_id === parseInt(id[0]) &&
                element.paragraph === parseInt(id[1]) &&
                element.word_start === parseInt(id[2]) &&
                element.word_end === parseInt(id[3]) &&
                element.channel_uid !== destChannel.id &&
                element.channel_uid !== srcChannel.id
            );
            return {
              key: item,
              sentId: item,
              pali: paliContent?.content,
              srcContent: srcContent?.content,
              destContent: destContent?.content,
            };
          });
          setDiffData(diffList);
          setNewRowKeys(newRows);
          if (important) {
            setSelectedRowKeys(sentences);
          } else {
            setSelectedRowKeys(newRows);
          }
          setEmptyRowKeys(emptyRows);
        }
      });
    }
  }, [srcChannel, sentences, destChannel]);

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
        <Select
          defaultValue={important ? "all" : "new"}
          style={{ width: 180 }}
          disabled={important}
          onChange={(value: string) => {
            switch (value) {
              case "new":
                setSelectedRowKeys(newRowKeys);
                break;
              case "all":
                setSelectedRowKeys(sentences);
                break;
              case "empty":
                setSelectedRowKeys(emptyRowKeys);
                break;
              default:
                break;
            }
          }}
          options={[
            { value: "new", label: "仅复制较新的" },
            { value: "empty", label: "仅复制缺失的" },
            { value: "all", label: "全部复制" },
          ]}
        />
        <Button
          type="primary"
          loading={loading}
          onClick={() => {
            if (typeof srcChannel === "undefined") {
              return;
            }
            if (
              typeof selectedRowKeys === "undefined" ||
              selectedRowKeys.length === 0
            ) {
              message.warning("没有被选择的句子");
              return;
            }
            setLoading(true);
            let submitData: ISentenceDiffData[] = [];
            selectedRowKeys?.forEach((value) => {
              const id: string[] = value.toString().split("-");
              const srcContent = srcApiData.find(
                (element) =>
                  element.book_id === parseInt(id[0]) &&
                  element.paragraph === parseInt(id[1]) &&
                  element.word_start === parseInt(id[2]) &&
                  element.word_end === parseInt(id[3]) &&
                  element.channel_uid === srcChannel.id
              );
              if (srcContent) {
                submitData.push(srcContent);
              }
            });

            if (typeof submitData === "undefined") {
              return;
            }
            const url = `/v2/sentence`;
            const postData = {
              sentences: submitData,
              channel: destChannel?.id,
              copy: true,
              fork_from: srcChannel.id,
            };
            console.debug("fork post", url, postData);
            post<ISentenceNewRequest, ISentenceListResponse>(url, postData)
              .then((json) => {
                if (json.ok) {
                  //发布数据
                  const newData: ISentence[] = json.data.rows.map((item) =>
                    toISentence(item)
                  );
                  store.dispatch(accept(newData));
                  if (typeof onSubmit !== "undefined") {
                    onSubmit();
                  }
                } else {
                  message.error(json.message);
                }
              })
              .catch((e) => {
                console.log(e);
                message.error("error");
              })
              .finally(() => {
                setLoading(false);
              });
          }}
        >
          开始复制
        </Button>
      </div>
      <div style={{ height: 400, overflowY: "scroll" }}>
        <Table
          pagination={false}
          rowSelection={{
            type: "checkbox",
            selectedRowKeys: selectedRowKeys,
            onChange: (
              selectedRowKeys: React.Key[],
              selectedRows: IDataType[]
            ) => {
              console.log(
                `selectedRowKeys: ${selectedRowKeys}`,
                "selectedRows: ",
                selectedRows
              );
              setSelectedRowKeys(selectedRowKeys);
            },
            getCheckboxProps: (record: IDataType) => ({
              name: record.pali ? record.pali : undefined,
            }),
          }}
          columns={[
            {
              title: "pali",
              width: "33%",
              dataIndex: "pali",
              render: (value, record, index) => {
                return (
                  <Text>
                    <div
                      dangerouslySetInnerHTML={{
                        __html: record.pali ? record.pali : "",
                      }}
                    />
                  </Text>
                );
              },
            },
            {
              title: (
                <>
                  {`原文-`}
                  <Text strong>{srcChannel?.name}</Text>
                </>
              ),
              width: "33%",
              dataIndex: "srcContent",
            },
            {
              title: (
                <>
                  {`复制到-`}
                  <Text strong>{destChannel?.name}</Text>
                </>
              ),
              width: "33%",
              dataIndex: "destContent",
              render: (value, record, index) => {
                const diff: Change[] = diffChars(
                  record.destContent ? record.destContent : "",
                  record.srcContent ? record.srcContent : ""
                );
                const diffResult = diff.map((item, id) => {
                  return (
                    <Text
                      key={id}
                      type={
                        item.added
                          ? "success"
                          : item.removed
                          ? "danger"
                          : "secondary"
                      }
                      delete={item.removed ? true : undefined}
                    >
                      {item.value}
                    </Text>
                  );
                });
                return (
                  <Tooltip title={record.destContent}>{diffResult}</Tooltip>
                );
              },
            },
          ]}
          dataSource={diffData}
        />
      </div>
    </div>
  );
};

export default ChannelSentDiffWidget;

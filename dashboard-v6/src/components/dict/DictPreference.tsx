import { ProList } from "@ant-design/pro-components";

import { EditOutlined, CheckOutlined } from "@ant-design/icons";
import type {
  IApiResponseDictData,
  IPreferenceListResponse,
  IPreferenceRequest,
  IPreferenceResponse,
} from "../../api/dict";
import { Button, Input, Space, Tag } from "antd";
import { get, put } from "../../request";

import { useEffect, useState } from "react";

import Lookup from "./Lookup";

import User from "../auth/User";
import DictConfidence from "./DictConfidence";
import { setValue } from "./utils";
import type { IWbw } from "../../types/wbw";
import WbwFactorsEditor from "../wbw/WbwFactorsEditor";
import WbwParentEditor from "../wbw/WbwParentEditor";
import WbwLookup from "../wbw/WbwLookup";

interface IOkButton {
  data: IApiResponseDictData;
  onChange?: (data: IApiResponseDictData) => void;
}
const OkButton = ({ data, onChange }: IOkButton) => {
  const [loading, setLoading] = useState(false);
  return (
    <Button
      type="link"
      icon={<CheckOutlined />}
      loading={loading}
      onClick={async () => {
        setLoading(true);
        const result = await setValue(data.id, 100);
        setLoading(false);
        console.info("api response", result);
        if (result.ok) {
          onChange?.(result.data);
        }
      }}
    >
      确认
    </Button>
  );
};

const toWbw = (data: IApiResponseDictData): IWbw => {
  return {
    book: 1,
    para: 1,
    sn: [1],
    word: { value: data.word, status: 5 },
    real: { value: data.word, status: 5 },
    factors: { value: data.factors ?? "", status: 5 },
    parent: { value: data.parent ?? "", status: 5 },
    confidence: data.confidence ?? 0,
  };
};
interface IFactorsEditorWidget {
  data: IApiResponseDictData;
}
const FactorsEditor = ({ data }: IFactorsEditorWidget) => {
  const [wbw, setWbw] = useState(toWbw(data));
  const [input, setInput] = useState(data.factors);
  const [type, setType] = useState(false);

  useEffect(() => {
    setWbw(toWbw(data));
  }, [data]);

  const upload = async (value: string) => {
    const url = `/api/v2/dict-preference/${data.id}`;
    const values: IPreferenceRequest = {
      factors: value,
      confidence: 100,
    };
    console.debug("api request", url, data);
    const result = await put<IPreferenceRequest, IPreferenceResponse>(
      url,
      values
    );
    console.info("api response", result);
    setWbw(toWbw(result.data));
    setInput(result.data.factors);
    return result;
  };
  return type ? (
    <div style={{ display: "flex" }}>
      <Input
        width={400}
        value={input ?? ""}
        placeholder="Title"
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          setInput(event.target.value);
        }}
      />
      <Button
        type="text"
        icon={<CheckOutlined />}
        onClick={async () => {
          upload(input ?? "");

          setType(false);
        }}
      />
    </div>
  ) : (
    <Space>
      <WbwFactorsEditor
        key="factors"
        initValue={wbw}
        display={"block"}
        onChange={async (e: string): Promise<IPreferenceResponse> => {
          const result = upload(e ?? "");
          return result;
        }}
      />
      <Button
        type="text"
        icon={<EditOutlined />}
        onClick={() => setType(true)}
      />
    </Space>
  );
};

interface IParentEditorWidget {
  data: IApiResponseDictData;
}
const ParentEditor = ({ data }: IParentEditorWidget) => {
  const [wbw, setWbw] = useState(toWbw(data));
  const [input, setInput] = useState(data.factors);
  const [type, setType] = useState(false);

  useEffect(() => {
    setWbw(toWbw(data));
  }, [data]);

  const upload = async (value: string) => {
    const url = `/api/v2/dict-preference/${data.id}`;
    const values: IPreferenceRequest = {
      parent: value,
      confidence: 100,
    };
    console.debug("api request", url, data);
    const result = await put<IPreferenceRequest, IPreferenceResponse>(
      url,
      values
    );
    console.info("api response", result);
    setWbw(toWbw(result.data));
    setInput(result.data.factors);
    return result;
  };
  return type ? (
    <div style={{ display: "flex" }}>
      <Input
        width={400}
        value={input ?? ""}
        placeholder="Title"
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          setInput(event.target.value);
        }}
      />
      <Button
        type="text"
        icon={<CheckOutlined />}
        onClick={async () => {
          upload(input ?? "");
          setType(false);
        }}
      />
    </div>
  ) : (
    <Space>
      <WbwParentEditor
        key="factors"
        initValue={wbw}
        display={"block"}
        onChange={async (e: string): Promise<IPreferenceResponse> => {
          const result = upload(e ?? "");
          return result;
        }}
      />
      <Button
        type="text"
        icon={<EditOutlined />}
        onClick={() => setType(true)}
      />
    </Space>
  );
};

export interface IDictPreferenceWidget {
  currPage?: number;
  pageSize?: number;
}

const DictPreference = ({
  currPage,
  pageSize = 100,
}: IDictPreferenceWidget) => {
  const [lookupWords, setLookupWords] = useState<string[]>([]);
  const [lookupRun, setLookupRun] = useState(false);
  const [data, setData] = useState<IApiResponseDictData[]>([]);
  return (
    <>
      <WbwLookup words={lookupWords} run={lookupRun} />

      <ProList<IApiResponseDictData>
        search={{
          filterType: "light",
        }}
        rowKey="name"
        headerTitle="单词首选项"
        dataSource={data}
        onDataSourceChange={setData}
        request={async (params = {}) => {
          let url = `/api/v2/dict-preference`;
          const mPageSize = pageSize ?? params.pageSize ?? 100;
          const offset = ((currPage ?? params.current ?? 1) - 1) * mPageSize;
          url += `?limit=${mPageSize}&offset=${offset}`;
          url += params.keyword ? "&keyword=" + params.keyword : "";
          console.info("api request", url);
          const res = await get<IPreferenceListResponse>(url);
          console.info("api response", res);

          return {
            data: res.data.rows.map((item, id) => {
              return { ...item, sn: id + offset + 1 };
            }),
            total: res.data.count,
            success: true,
          };
        }}
        pagination={
          currPage
            ? false
            : {
                showQuickJumper: !currPage,
                showSizeChanger: !currPage,
                showLessItems: !currPage,
                showPrevNextJumpers: !currPage,
                pageSize: 100,
              }
        }
        onRow={(record) => {
          return {
            onMouseEnter: () => {
              console.info(`点击了行：${record.word}`);
              setLookupWords([record.word]);
              setLookupRun(true);
            },
            onMouseLeave: () => {
              setLookupRun(false);
            },
          };
        }}
        metas={{
          title: {
            dataIndex: "word",
            title: "用户",
            render(_dom, entity) {
              return (
                <Space>
                  {`[${entity.sn}]`}
                  <Lookup search={entity.word}>{entity.word}</Lookup>
                </Space>
              );
            },
          },
          avatar: {
            dataIndex: "sn",
            search: false,
            render(_dom, entity) {
              return (
                <User
                  {...entity.editor}
                  showName={false}
                  showUserName={false}
                />
              );
            },
          },
          description: {
            dataIndex: "title",
            search: false,
            render(_dom, entity) {
              return (
                <Space>
                  <FactorsEditor data={entity} />
                  <span>|</span>
                  <ParentEditor data={entity} />
                </Space>
              );
            },
          },
          subTitle: {
            dataIndex: "labels",
            render: (_, row) => {
              return (
                <Space>
                  <Tag color="blue" key={row.count}>
                    {row.count}
                  </Tag>
                  <DictConfidence
                    value={row.confidence}
                    onChange={async (value) => {
                      const result = await setValue(row.id, value);
                      setData((origin) => {
                        origin.forEach((value, index, array) => {
                          if (value.id === result.data.id) {
                            array[index] = {
                              ...value,
                              confidence: result.data.confidence,
                            };
                          }
                        });
                        return origin;
                      });
                    }}
                  />
                </Space>
              );
            },
            search: false,
          },

          actions: {
            render: (_text, row) => {
              return [
                <OkButton
                  data={row}
                  onChange={(data) => {
                    setData((origin) => {
                      origin.forEach((value, index, array) => {
                        if (value.id === row.id) {
                          array[index] = {
                            ...value,
                            confidence: data.confidence,
                          };
                        }
                      });
                      return origin;
                    });
                  }}
                />,
              ];
            },
            search: false,
          },
          status: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "状态",
            valueType: "select",
            valueEnum: {
              all: { text: "全部", status: "Default" },
              open: {
                text: "未解决",
                status: "Error",
              },
              closed: {
                text: "已解决",
                status: "Success",
              },
              processing: {
                text: "解决中",
                status: "Processing",
              },
            },
          },
        }}
      />
    </>
  );
};

export default DictPreference;

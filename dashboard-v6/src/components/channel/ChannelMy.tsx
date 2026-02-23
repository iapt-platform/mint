import { useCallback, useEffect, useState } from "react";
import { useIntl } from "react-intl";
import type { Key } from "antd/es/table/interface";
import {
  Badge,
  Button,
  Card,
  Dropdown,
  Input,
  Select,
  Skeleton,
  Space,
  Tag,
  Tooltip,
  Tree,
} from "antd";
import {
  GlobalOutlined,
  EditOutlined,
  ReloadOutlined,
  MoreOutlined,
  CopyOutlined,
  InfoCircleOutlined,
} from "@ant-design/icons";

import { get, post } from "../../request";
import type {
  IApiResponseChannelList,
  IChannel,
  ISentInChapterListResponse,
} from "../../api/Channel";
import type { IItem, IProgressRequest } from "./ChannelPickerTable";
import { LockFillIcon, LockIcon } from "../../assets/icon";
import StudioName from "../auth/Studio";
import ProgressSvg from "./ProgressSvg";

import CopyToModal from "./CopyToModal";

import { ChannelInfoModal } from "./ChannelInfo";

import type { ArticleType } from "../../api/Corpus";
import { getSentIdInArticle } from "./utils";
import TokenModal from "../token/TokenModal";
import NissayaAlignerModal from "../nissaya/NissayaAlignerModal";

const { Search } = Input;

interface IToken {
  channelId?: string;
  articleId?: string;
  type?: ArticleType;
}

interface ChannelTreeNode {
  key: string;
  title: string | React.ReactNode;
  channel: IItem;
  icon?: React.ReactNode;
  children?: ChannelTreeNode[];
}

interface IWidget {
  type?: ArticleType | "editable";
  articleId?: string;
  selectedKeys?: string[];
  style?: React.CSSProperties;
  onSelect?: (selected: IChannel[]) => void;
}
const ChannelMy = ({
  type,
  articleId,
  selectedKeys = [],
  style,
  onSelect,
}: IWidget) => {
  const intl = useIntl();
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  const [treeData, setTreeData] = useState<ChannelTreeNode[]>();
  const [dirty, setDirty] = useState(false);
  const [channels, setChannels] = useState<IItem[]>([]);
  const [owner, setOwner] = useState("all");
  const [search, setSearch] = useState<string>();
  const [loading, setLoading] = useState(true);
  const [copyChannel, setCopyChannel] = useState<IChannel>();
  const [nissayaOpen, setNissayaOpen] = useState(false);
  const [copyOpen, setCopyOpen] = useState<boolean>(false);
  const [infoOpen, setInfoOpen] = useState<boolean>(false);
  const [statistic, setStatistic] = useState<IItem>();
  const [sentenceCount, setSentenceCount] = useState<number>(0);
  const [sentencesId, setSentencesId] = useState<string[]>();
  const [token, SetToken] = useState<IToken>();
  const [tokenOpen, setTokenOpen] = useState(false);

  console.debug("ChannelMy render", type, articleId);

  //TODO remove useEffect
  const loadChannel = useCallback(async (sentences: string[]) => {
    setSentenceCount(sentences.length);
    setLoading(true);

    try {
      const res = await post<IProgressRequest, IApiResponseChannelList>(
        "/api/v2/channel-progress",
        {
          sentence: sentences,
          owner: "all",
        }
      );

      const items: IItem[] = res.data.rows
        .filter((v) => !v.name.startsWith("_sys"))
        .map((item, id) => {
          const date = new Date(item.created_at);

          let all = 0;
          let finished = 0;

          item.final?.forEach((v) => {
            all += v[0];
            if (v[1]) finished += v[0];
          });

          return {
            id,
            uid: item.uid,
            title: item.name,
            summary: item.summary,
            studio: item.studio,
            shareType: "my",
            role: item.role,
            type: item.type,
            publicity: item.status,
            createdAt: date.getTime(),
            final: item.final,
            progress: all ? finished / all : 0,
            content_created_at: item.content_created_at,
            content_updated_at: item.content_updated_at,
          };
        });

      setChannels(items);
    } finally {
      setLoading(false);
    }
  }, []);

  const load = useCallback(async () => {
    let sentList: string[] = [];

    if (type === "chapter") {
      const id = articleId?.split("-");
      if (id?.length === 2) {
        const url = `/api/v2/sentences-in-chapter?book=${id[0]}&para=${id[1]}`;

        try {
          const res = await get<ISentInChapterListResponse>(url);
          if (!res?.ok) return;

          sentList = res.data.rows.map(
            (item) =>
              `${item.book}-${item.paragraph}-${item.word_begin}-${item.word_end}`
          );
        } catch (err) {
          console.error(err);
          return;
        }
      }
    } else {
      sentList = getSentIdInArticle();
    }

    setSentencesId(sentList);
    await loadChannel(sentList);
  }, [type, articleId, loadChannel]);

  useEffect(() => {
    load();
  }, [load]);

  useEffect(() => {
    setSelectedRowKeys(selectedKeys);
  }, [selectedKeys]);

  useEffect(() => {
    sortChannels(channels);
  }, [channels, selectedRowKeys, owner]);

  interface IChannelFilter {
    key?: string;
    owner?: string;
    selectedRowKeys?: React.Key[];
  }

  const sortChannels = (channelList: IItem[], filter?: IChannelFilter) => {
    const mOwner = filter?.owner ?? owner;
    if (mOwner === "my") {
      //我自己的
      const myChannel = channelList.filter((value) => value.role === "owner");
      const data = myChannel.map((item) => {
        return { key: item.uid, title: item.title, channel: item };
      });
      setTreeData(data);
    } else {
      //当前被选择的
      const selectedChannel: IItem[] = [];
      const mSelectedRowKeys = filter?.selectedRowKeys ?? selectedRowKeys;
      mSelectedRowKeys.forEach((channelId) => {
        const channel = channelList.find((value) => value.uid === channelId);
        if (channel) {
          selectedChannel.push(channel);
        }
      });
      let show = mSelectedRowKeys;
      //有进度的
      const progressing = channelList.filter(
        (value) => value.progress > 0 && !show.includes(value.uid)
      );
      show = [...show, ...progressing.map((item) => item.uid)];
      //我自己的
      const myChannel = channelList.filter(
        (value) => value.role === "owner" && !show.includes(value.uid)
      );
      show = [...show, ...myChannel.map((item) => item.uid)];
      //其他的
      const others = channelList.filter(
        (value) => !show.includes(value.uid) && value.role !== "member"
      );
      let channelData = [
        ...selectedChannel,
        ...progressing,
        ...myChannel,
        ...others,
      ];

      const key = filter?.key ?? search;
      if (key) {
        channelData = channelData.filter((value) => value.title.includes(key));
      }

      const data = channelData.map((item) => {
        return { key: item.uid, title: item.title, channel: item };
      });
      setTreeData(data);
    }
  };

  return (
    <div style={style}>
      <TokenModal
        {...token}
        open={tokenOpen}
        onClose={() => setTokenOpen(false)}
      />
      <Card
        size="small"
        title={
          <Space>
            <Search
              placeholder="版本名称"
              onSearch={(value) => {
                console.debug(value);
                setSearch(value);
                sortChannels(channels, { key: value });
              }}
              style={{ width: 120 }}
            />
            <Select
              defaultValue="all"
              style={{ width: 80 }}
              bordered={false}
              options={[
                {
                  value: "all",
                  label: intl.formatMessage({ id: "buttons.channel.all" }),
                },
                {
                  value: "my",
                  label: intl.formatMessage({ id: "buttons.channel.my" }),
                },
              ]}
              onSelect={(value: string) => {
                setOwner(value);
              }}
            />
          </Space>
        }
        extra={
          <Space size={"small"}>
            <Button
              size="small"
              type="link"
              disabled={!dirty}
              onClick={() => {
                if (typeof onSelect !== "undefined") {
                  setDirty(false);
                  const selected: IChannel[] = selectedRowKeys.map((item) => {
                    return {
                      id: item.toString(),
                      name:
                        treeData?.find((value) => value.channel.uid === item)
                          ?.channel.title ?? "",
                    };
                  });
                  onSelect(selected);
                }
              }}
            >
              {intl.formatMessage({
                id: "buttons.ok",
              })}
            </Button>
            <Button
              size="small"
              type="link"
              disabled={!dirty}
              onClick={() => {
                setSelectedRowKeys(selectedKeys);
                setDirty(false);
              }}
            >
              {intl.formatMessage({
                id: "buttons.cancel",
              })}
            </Button>
            <Button
              type="link"
              size="small"
              icon={<ReloadOutlined />}
              onClick={() => {
                load();
              }}
            />
          </Space>
        }
      >
        {loading ? (
          <Skeleton active />
        ) : (
          <Tree
            selectedKeys={selectedRowKeys}
            multiple
            checkedKeys={selectedRowKeys}
            checkable
            treeData={treeData}
            blockNode
            onCheck={(
              checked: Key[] | { checked: Key[]; halfChecked: Key[] }
            ) => {
              setDirty(true);
              if (Array.isArray(checked)) {
                if (checked.length > selectedRowKeys.length) {
                  const add = checked.filter(
                    (value) => !selectedRowKeys.includes(value.toString())
                  );
                  if (add.length > 0) {
                    setSelectedRowKeys([...selectedRowKeys, add[0]]);
                  }
                } else {
                  setSelectedRowKeys(
                    selectedRowKeys.filter((value) => checked.includes(value))
                  );
                }
              }
            }}
            onSelect={() => {}}
            titleRender={(node: ChannelTreeNode) => {
              let pIcon = <></>;
              switch (node.channel.publicity) {
                case 5:
                  pIcon = (
                    <Tooltip title={"私有不可公开"}>
                      <LockFillIcon />
                    </Tooltip>
                  );
                  break;
                case 10:
                  pIcon = (
                    <Tooltip title={"私有"}>
                      <LockIcon />
                    </Tooltip>
                  );
                  break;
                case 30:
                  pIcon = (
                    <Tooltip title={"公开"}>
                      <GlobalOutlined />
                    </Tooltip>
                  );
                  break;
              }
              const badge = selectedRowKeys.findIndex(
                (value) => value === node.channel.uid
              );
              return (
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    width: "100%",
                  }}
                >
                  <div
                    style={{
                      width: "100%",
                      borderRadius: 5,
                      padding: "0 5px",
                    }}
                    onClick={() => {
                      console.log(node);
                      if (channels) {
                        sortChannels(channels);
                      }
                      setDirty(false);
                      if (typeof onSelect !== "undefined") {
                        onSelect([
                          {
                            id: node.key,
                            name: node.channel.title,
                          },
                        ]);
                      }
                    }}
                  >
                    <div
                      key="info"
                      style={{ overflowX: "clip", display: "flex" }}
                    >
                      <Space>
                        {pIcon}
                        {node.channel.role !== "member" ? (
                          <EditOutlined />
                        ) : undefined}
                      </Space>
                      <Button type="link">
                        <Space>
                          <StudioName data={node.channel.studio} hideName />
                          <>{node.channel.title}</>
                          <Tag>
                            {intl.formatMessage({
                              id: `channel.type.${node.channel.type}.label`,
                            })}
                          </Tag>
                        </Space>
                      </Button>
                    </div>
                    <div key="progress">
                      <ProgressSvg data={node.channel.final} width={200} />
                    </div>
                  </div>
                  <Badge count={dirty ? badge + 1 : 0}>
                    <div>
                      <Dropdown
                        trigger={["click"]}
                        menu={{
                          items: [
                            {
                              key: "copy-to",
                              label: intl.formatMessage({
                                id: "buttons.copy.to",
                              }),
                              icon: <CopyOutlined />,
                            },
                            {
                              key: "import-nissaya",
                              label: intl.formatMessage({
                                id: "buttons.import",
                              }),
                              icon: <CopyOutlined />,
                            },
                            {
                              key: "statistic",
                              label: intl.formatMessage({
                                id: "buttons.statistic",
                              }),
                              icon: <InfoCircleOutlined />,
                            },
                            {
                              key: "token",
                              label: intl.formatMessage({
                                id: "buttons.access-token.get",
                              }),
                              icon: <InfoCircleOutlined />,
                            },
                          ],
                          onClick: (e) => {
                            switch (e.key) {
                              case "copy-to":
                                setCopyChannel({
                                  id: node.channel.uid,
                                  name: node.channel.title,
                                  type: node.channel.type,
                                });
                                setCopyOpen(true);
                                break;
                              case "import-nissaya":
                                setCopyChannel({
                                  id: node.channel.uid,
                                  name: node.channel.title,
                                  type: node.channel.type,
                                });
                                setNissayaOpen(true);
                                break;
                              case "statistic":
                                setInfoOpen(true);
                                setStatistic(node.channel);
                                break;
                              case "token":
                                SetToken({
                                  channelId: node.channel.uid,
                                  type: type as ArticleType,
                                  articleId: articleId,
                                });
                                setTokenOpen(true);
                                break;
                              default:
                                break;
                            }
                          },
                        }}
                        placement="bottomRight"
                      >
                        <Button
                          type="link"
                          size="small"
                          icon={<MoreOutlined />}
                        ></Button>
                      </Dropdown>
                    </div>
                  </Badge>
                </div>
              );
            }}
          />
        )}
      </Card>
      <CopyToModal
        sentencesId={sentencesId}
        channel={copyChannel}
        open={copyOpen}
        onClose={() => setCopyOpen(false)}
      />
      <NissayaAlignerModal
        sentencesId={sentencesId}
        channel={copyChannel}
        open={nissayaOpen}
        onClose={() => setNissayaOpen(false)}
      />
      <ChannelInfoModal
        sentenceCount={sentenceCount}
        channel={statistic}
        open={infoOpen}
        onClose={() => setInfoOpen(false)}
      />
    </div>
  );
};
export default ChannelMy;

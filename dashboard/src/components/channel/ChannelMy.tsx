import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Key } from "antd/es/table/interface";
import {
  Badge,
  Button,
  Card,
  Dropdown,
  Input,
  Select,
  Skeleton,
  Space,
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
import {
  IApiResponseChannelList,
  ISentInChapterListResponse,
} from "../api/Channel";
import { IItem, IProgressRequest } from "./ChannelPickerTable";
import { LockFillIcon, LockIcon } from "../../assets/icon";
import StudioName from "../auth/Studio";
import ProgressSvg from "./ProgressSvg";

import { IChannel } from "./Channel";
import CopyToModal from "./CopyToModal";
import { ArticleType } from "../article/Article";
import { ChannelInfoModal } from "./ChannelInfo";

const { Search } = Input;

export const getSentIdInArticle = () => {
  let sentList: string[] = [];
  const sentElement = document.querySelectorAll(".pcd_sent");
  for (let index = 0; index < sentElement.length; index++) {
    const element = sentElement[index];
    const id = element.id.split("_")[1];
    sentList.push(id);
  }
  return sentList;
};

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
  onSelect?: Function;
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
  const [copyOpen, setCopyOpen] = useState<boolean>(false);
  const [infoOpen, setInfoOpen] = useState<boolean>(false);
  const [statistic, setStatistic] = useState<IItem>();
  const [sentenceCount, setSentenceCount] = useState<number>(0);
  const [sentencesId, setSentencesId] = useState<string[]>();

  console.debug("ChannelMy render", type, articleId);

  //TODO remove useEffect

  useEffect(() => {
    load();
  }, [type, articleId]);

  useEffect(() => {
    if (selectedRowKeys.join() !== selectedKeys.join()) {
      setSelectedRowKeys(selectedKeys);
    }
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
      const data = myChannel.map((item, index) => {
        return { key: item.uid, title: item.title, channel: item };
      });
      setTreeData(data);
    } else {
      //当前被选择的
      let selectedChannel: IItem[] = [];
      let mSelectedRowKeys = filter?.selectedRowKeys ?? selectedRowKeys;
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

      const data = channelData.map((item, index) => {
        return { key: item.uid, title: item.title, channel: item };
      });
      setTreeData(data);
    }
  };
  const load = () => {
    let sentList: string[] = [];
    if (type === "chapter") {
      const id = articleId?.split("-");
      if (id?.length === 2) {
        const url = `/v2/sentences-in-chapter?book=${id[0]}&para=${id[1]}`;
        console.info("ChannelMy url", url);
        get<ISentInChapterListResponse>(url)
          .then((res) => {
            console.debug("ChannelMy ISentInChapterListResponse", res);
            if (res && res.ok) {
              sentList = res.data.rows.map((item) => {
                return `${item.book}-${item.paragraph}-${item.word_begin}-${item.word_end}`;
              });
              setSentencesId(sentList);
              loadChannel(sentList);
            } else {
              console.error("res", res);
            }
          })
          .catch((reason: any) => {
            console.error(reason);
          });
      }
    } else {
      sentList = getSentIdInArticle();
      setSentencesId(sentList);
      loadChannel(sentList);
    }
  };

  function loadChannel(sentences: string[]) {
    setSentenceCount(sentences.length);
    console.debug("sentences", sentences);
    const currOwner = "all";

    console.log("owner", currOwner);
    setLoading(true);
    post<IProgressRequest, IApiResponseChannelList>(`/v2/channel-progress`, {
      sentence: sentences,
      owner: currOwner,
    })
      .then((res) => {
        console.debug("progress data", res.data.rows);
        const items: IItem[] = res.data.rows
          .filter((value) => value.name.substring(0, 4) !== "_Sys")
          .map((item, id) => {
            const date = new Date(item.created_at);
            let all: number = 0;
            let finished: number = 0;
            item.final?.forEach((value) => {
              all += value[0];
              finished += value[1] ? value[0] : 0;
            });
            const progress = finished / all;
            return {
              id: id,
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
              progress: progress,
              content_created_at: item.content_created_at,
              content_updated_at: item.content_updated_at,
            };
          });

        setChannels(items);
      })
      .finally(() => {
        setLoading(false);
      });
  }

  return (
    <div style={style}>
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
                  onSelect(
                    selectedRowKeys.map((item) => {
                      return {
                        id: item,
                        name: treeData?.find(
                          (value) => value.channel.uid === item
                        )?.channel.title,
                      };
                    })
                  );
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
            onSelect={(keys: Key[]) => {}}
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
                    onClick={(
                      e: React.MouseEvent<HTMLSpanElement, MouseEvent>
                    ) => {
                      console.log(node);
                      if (channels) {
                        sortChannels(channels);
                      }
                      setDirty(false);
                      if (typeof onSelect !== "undefined") {
                        onSelect([
                          {
                            id: node.key,
                            name: node.title,
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
                          {node.channel.title}
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
                              key: "statistic",
                              label: intl.formatMessage({
                                id: "buttons.statistic",
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
                              case "statistic":
                                setInfoOpen(true);
                                setStatistic(node.channel);
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

import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Key } from "antd/es/table/interface";
import { Badge, Button, Card, Select, Space, Tree } from "antd";
import {
  GlobalOutlined,
  EditOutlined,
  ReloadOutlined,
} from "@ant-design/icons";

import { post } from "../../request";
import { IApiResponseChannelList } from "../api/Channel";
import { IItem, IProgressRequest } from "./ChannelPickerTable";
import { LockIcon } from "../../assets/icon";
import StudioName from "../auth/StudioName";
import ProgressSvg from "./ProgressSvg";
import { DataNode, EventDataNode } from "antd/es/tree";

interface ChannelTreeNode {
  key: string;
  title: string | React.ReactNode;
  channel: IItem;
  icon?: React.ReactNode;
  children?: ChannelTreeNode[];
}

interface IWidget {
  selectedKeys?: string[];
  style?: React.CSSProperties;
  onSelect?: Function;
}
const ChannelMy = ({ selectedKeys = [], style, onSelect }: IWidget) => {
  const intl = useIntl();
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  const [treeData, setTreeData] = useState<ChannelTreeNode[]>();
  const [dirty, setDirty] = useState(false);
  const [channels, setChannels] = useState<IItem[]>([]);

  useEffect(() => load("all"), []);

  useEffect(() => {
    setSelectedRowKeys(selectedKeys);
  }, [selectedKeys]);

  useEffect(() => {
    sortChannels(channels);
  }, [channels, selectedRowKeys]);

  const sortChannels = (channelList: IItem[]) => {
    //当前被选择的
    const currChannel = channelList.filter((value) =>
      selectedRowKeys.includes(value.uid)
    );
    let show = selectedRowKeys;
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
    const channelData = [
      ...currChannel,
      ...progressing,
      ...myChannel,
      ...others,
    ];
    const data = channelData.map((item, index) => {
      return { key: item.uid, title: item.title, channel: item };
    });
    setTreeData(data);
  };
  const load = (owner: string) => {
    const sentElement = document.querySelectorAll(".pcd_sent");
    let sentList: string[] = [];
    for (let index = 0; index < sentElement.length; index++) {
      const element = sentElement[index];
      const id = element.id.split("_")[1];
      sentList.push(id);
    }
    const currOwner = owner ? owner : "all";
    if (owner) {
      //setOwnerChanged(true);
    }
    console.log("owner", currOwner);
    post<IProgressRequest, IApiResponseChannelList>(`/v2/channel-progress`, {
      sentence: sentList,
      owner: currOwner,
    }).then((res) => {
      console.log("progress data", res.data.rows);
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
          };
        });

      setChannels(items);
    });
  };
  return (
    <div style={style}>
      <Card
        size="small"
        title={
          <Select
            defaultValue="all"
            style={{ width: 120 }}
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
              {
                value: "collaborator",
                label: intl.formatMessage({
                  id: "buttons.channel.collaborator",
                }),
              },
              {
                value: "public",
                label: intl.formatMessage({
                  id: "buttons.channel.public",
                }),
              },
            ]}
          />
        }
        extra={
          <Space>
            <Button
              type="primary"
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
              确定
            </Button>
            <Button type="link" icon={<ReloadOutlined />} />
          </Space>
        }
      >
        <Tree
          selectedKeys={selectedRowKeys}
          multiple
          checkedKeys={selectedRowKeys}
          checkable
          treeData={treeData}
          blockNode
          onClick={(
            e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
            node: EventDataNode<DataNode>
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
              case 10:
                pIcon = <LockIcon />;
                break;
              case 30:
                pIcon = <GlobalOutlined />;
                break;
            }
            const badge = selectedRowKeys.findIndex(
              (value) => value === node.channel.uid
            );
            return (
              <Badge count={dirty ? badge + 1 : 0}>
                <div
                  style={{
                    width: "100%",
                    borderRadius: 5,
                    padding: "0 5px",
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
                        <StudioName
                          data={node.channel.studio}
                          showName={false}
                        />
                        {node.channel.title}
                      </Space>
                    </Button>
                  </div>
                  <div key="progress">
                    <ProgressSvg data={node.channel.final} width={200} />
                  </div>
                </div>
              </Badge>
            );
          }}
        />
      </Card>
    </div>
  );
};
export default ChannelMy;

// src/components/channel/ChannelMy.tsx

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useIntl } from "react-intl";
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
import type { Key } from "antd/es/table/interface";
import {
  GlobalOutlined,
  EditOutlined,
  ReloadOutlined,
  MoreOutlined,
  CopyOutlined,
  InfoCircleOutlined,
} from "@ant-design/icons";

import { LockFillIcon, LockIcon } from "../../assets/icon";
import StudioName from "../auth/Studio";
import ProgressSvg from "./ProgressSvg";
import CopyToModal from "./CopyToModal";
import { ChannelInfoModal } from "./ChannelInfo";
import type { ArticleType } from "../../api/Article";
import TokenModal from "../token/TokenModal";
import NissayaAlignerModal from "../nissaya/NissayaAlignerModal";
import { useChannelProgress } from "./hooks/useChannelProgress";
import type { IChannel, IChannelItem } from "../../api/channel";

const { Search } = Input;

// ─── 类型 ────────────────────────────────────────────────────────────────────

interface IToken {
  channelId?: string;
  articleId?: string;
  type?: ArticleType;
}

interface ChannelTreeNode {
  key: string;
  title: string | React.ReactNode;
  channel: IChannelItem;
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

// ─── 纯函数：对频道列表排序 / 过滤，不依赖任何 state ────────────────────────

function buildTreeData(
  channelList: IChannelItem[],
  selectedRowKeys: React.Key[],
  owner: string,
  search?: string
): ChannelTreeNode[] {
  let ordered: IChannelItem[];

  if (owner === "my") {
    ordered = channelList.filter((v) => v.role === "owner");
  } else {
    const selectedSet = new Set(selectedRowKeys.map(String));

    const selected = channelList.filter((v) => selectedSet.has(v.uid));
    const seen = new Set(selected.map((v) => v.uid));

    const progressing = channelList.filter(
      (v) => v.progress > 0 && !seen.has(v.uid)
    );
    progressing.forEach((v) => seen.add(v.uid));

    const mine = channelList.filter(
      (v) => v.role === "owner" && !seen.has(v.uid)
    );
    mine.forEach((v) => seen.add(v.uid));

    const others = channelList.filter(
      (v) => !seen.has(v.uid) && v.role !== "member"
    );

    ordered = [...selected, ...progressing, ...mine, ...others];
  }

  if (search) {
    ordered = ordered.filter((v) => v.title.includes(search));
  }

  return ordered.map((item) => ({
    key: item.uid,
    title: item.title,
    channel: item,
  }));
}

// ─── 组件 ────────────────────────────────────────────────────────────────────

const ChannelMy = ({
  type,
  articleId,
  selectedKeys = [],
  style,
  onSelect,
}: IWidget) => {
  const intl = useIntl();
  console.debug("ChannelMy render");
  // ── 远程数据（hook 负责全部异步逻辑）──────────────────────────────────────
  const { channels, sentencesId, sentenceCount, loading, refresh } =
    useChannelProgress(type, articleId);

  // ── 局部 UI 状态 ──────────────────────────────────────────────────────────
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  const [dirty, setDirty] = useState(false);
  const [owner, setOwner] = useState("all");
  const [search, setSearch] = useState<string>();

  // modal 状态
  const [copyChannel, setCopyChannel] = useState<IChannel>();
  const [nissayaOpen, setNissayaOpen] = useState(false);
  const [copyOpen, setCopyOpen] = useState(false);
  const [infoOpen, setInfoOpen] = useState(false);
  const [statistic, setStatistic] = useState<IChannelItem>();
  const [token, setToken] = useState<IToken>();
  const [tokenOpen, setTokenOpen] = useState(false);

  // ── selectedKeys prop 同步：用 JSON 序列化做稳定比较，避免数组引用变化触发循环 ──
  // 父组件每次 render 传入新数组字面量（如 selectedKeys={[]}）时，
  // 若直接放进 useEffect 依赖，引用每次都不同，会无限触发。
  const selectedKeysKey = JSON.stringify(selectedKeys);
  const selectedKeysRef = useRef(selectedKeys);
  useEffect(() => {
    selectedKeysRef.current = selectedKeys;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedKeysKey]);

  useEffect(() => {
    setSelectedRowKeys(selectedKeysRef.current);
  }, [selectedKeysKey]);

  // ── 派生数据：useMemo 替代 useEffect + setState，不产生额外渲染轮次 ──────
  const treeData = useMemo(
    () => buildTreeData(channels, selectedRowKeys, owner, search),
    [channels, selectedRowKeys, owner, search]
  );

  // ── 回调 ──────────────────────────────────────────────────────────────────

  const handleConfirm = useCallback(() => {
    if (!onSelect) return;
    setDirty(false);
    const selected: IChannel[] = selectedRowKeys.map((item) => ({
      id: item.toString(),
      name: treeData.find((v) => v.channel.uid === item)?.channel.title ?? "",
    }));
    onSelect(selected);
  }, [onSelect, selectedRowKeys, treeData]);

  const handleCancel = useCallback(() => {
    setSelectedRowKeys(selectedKeysRef.current);
    setDirty(false);
  }, []);

  const handleCheck = useCallback(
    (checked: Key[] | { checked: Key[]; halfChecked: Key[] }) => {
      setDirty(true);
      if (!Array.isArray(checked)) return;

      if (checked.length > selectedRowKeys.length) {
        const add = checked.filter(
          (v) => !selectedRowKeys.includes(v.toString())
        );
        if (add.length > 0) {
          setSelectedRowKeys((prev) => [...prev, add[0]]);
        }
      } else {
        setSelectedRowKeys(selectedRowKeys.filter((v) => checked.includes(v)));
      }
    },
    [selectedRowKeys]
  );

  const handleNodeClick = useCallback(
    (node: ChannelTreeNode) => {
      setDirty(false);
      onSelect?.([{ id: node.key, name: node.channel.title }]);
    },
    [onSelect]
  );

  // ── titleRender ───────────────────────────────────────────────────────────

  const titleRender = useCallback(
    (node: ChannelTreeNode) => {
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

      const badgeIndex = selectedRowKeys.findIndex(
        (v) => v === node.channel.uid
      );

      return (
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            width: "100%",
          }}
        >
          {/* 左侧：频道信息 + 进度 */}
          <div
            style={{ width: "100%", borderRadius: 5, padding: "0 5px" }}
            onClick={() => handleNodeClick(node)}
          >
            <div key="info" style={{ overflowX: "clip", display: "flex" }}>
              <Space>
                {pIcon}
                {node.channel.role !== "member" ? <EditOutlined /> : undefined}
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

          {/* 右侧：更多菜单 */}
          <Badge count={dirty ? badgeIndex + 1 : 0}>
            <div>
              <Dropdown
                trigger={["click"]}
                menu={{
                  items: [
                    {
                      key: "copy-to",
                      label: intl.formatMessage({ id: "buttons.copy.to" }),
                      icon: <CopyOutlined />,
                    },
                    {
                      key: "import-nissaya",
                      label: intl.formatMessage({ id: "buttons.import" }),
                      icon: <CopyOutlined />,
                    },
                    {
                      key: "statistic",
                      label: intl.formatMessage({ id: "buttons.statistic" }),
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
                    const ch: IChannel = {
                      id: node.channel.uid,
                      name: node.channel.title,
                      type: node.channel.type,
                    };
                    switch (e.key) {
                      case "copy-to":
                        setCopyChannel(ch);
                        setCopyOpen(true);
                        break;
                      case "import-nissaya":
                        setCopyChannel(ch);
                        setNissayaOpen(true);
                        break;
                      case "statistic":
                        setStatistic(node.channel);
                        setInfoOpen(true);
                        break;
                      case "token":
                        setToken({
                          channelId: node.channel.uid,
                          type: type as ArticleType,
                          articleId,
                        });
                        setTokenOpen(true);
                        break;
                    }
                  },
                }}
                placement="bottomRight"
              >
                <Button type="link" size="small" icon={<MoreOutlined />} />
              </Dropdown>
            </div>
          </Badge>
        </div>
      );
    },
    [dirty, selectedRowKeys, handleNodeClick, intl, type, articleId]
  );

  // ── 渲染 ──────────────────────────────────────────────────────────────────

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
                setSearch(value);
              }}
              style={{ width: 120 }}
            />
            <Select
              defaultValue="all"
              style={{ width: 80 }}
              variant="borderless"
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
              onSelect={(value: string) => setOwner(value)}
            />
          </Space>
        }
        extra={
          <Space size="small">
            <Button
              size="small"
              type="link"
              disabled={!dirty}
              onClick={handleConfirm}
            >
              {intl.formatMessage({ id: "buttons.ok" })}
            </Button>
            <Button
              size="small"
              type="link"
              disabled={!dirty}
              onClick={handleCancel}
            >
              {intl.formatMessage({ id: "buttons.cancel" })}
            </Button>
            <Button
              type="link"
              size="small"
              icon={<ReloadOutlined />}
              onClick={refresh}
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
            onCheck={handleCheck}
            onSelect={() => {}}
            titleRender={titleRender}
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

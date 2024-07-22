import { Divider, Popconfirm, Space, Tooltip, Typography } from "antd";
import { LikeOutlined, DeleteOutlined } from "@ant-design/icons";

import { ISentence } from "../SentEdit";
import { useEffect, useState } from "react";
import PrAcceptButton from "./PrAcceptButton";
import { HandOutlinedIcon } from "../../../assets/icon";
import store from "../../../store";
import { count, show } from "../../../reducers/discussion";
import { useAppSelector } from "../../../hooks";
import { openPanel } from "../../../reducers/right-panel";
import { useIntl } from "react-intl";
import SuggestionPopover from "./SuggestionPopover";
import DiscussionButton from "../../discussion/DiscussionButton";
import SuggestionButton from "./SuggestionButton";
import InteractiveButton from "./InteractiveButton";

const { Paragraph } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  style?: React.CSSProperties;
  compact?: boolean;
  prOpen?: boolean;
  onPrClose?: Function;
  onAccept?: Function;
  onDelete?: Function;
}
const SuggestionToolbarWidget = ({
  data,
  isPr = false,
  onAccept,
  style,
  prOpen = false,
  compact = false,
  onPrClose,
  onDelete,
}: IWidget) => {
  const intl = useIntl();

  return (
    <Paragraph type="secondary" style={style}>
      {isPr ? (
        <Space>
          <LikeOutlined />
          <Divider type="vertical" />
          <PrAcceptButton
            data={data}
            onAccept={(value: ISentence) => {
              if (typeof onAccept !== "undefined") {
                onAccept(value);
              }
            }}
          />
          <Popconfirm
            title={intl.formatMessage({
              id: "message.delete.confirm",
            })}
            placement="right"
            onConfirm={() => {
              if (typeof onDelete !== "undefined") {
                onDelete();
              }
            }}
            okType="danger"
            okText={intl.formatMessage({
              id: `buttons.delete`,
            })}
            cancelText={intl.formatMessage({
              id: `buttons.no`,
            })}
          >
            <Tooltip
              title={intl.formatMessage({
                id: `buttons.delete`,
              })}
            >
              <DeleteOutlined />
            </Tooltip>
          </Popconfirm>
        </Space>
      ) : (
        <InteractiveButton data={data} compact={compact} />
      )}
    </Paragraph>
  );
};

export default SuggestionToolbarWidget;

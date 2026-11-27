import { Avatar, Space, Tooltip, Typography } from "antd";
import { useIntl } from "react-intl";
import TimeShow from "../general/TimeShow";
import { getAvatarColor } from "./utils";

const { Text } = Typography;

/** 只用到头像与名字，v2 的 IUser 与 v3 的 actor 摘要都满足 */
export interface IEditorBrief {
  nickName?: string;
  userName?: string;
  avatar?: string | null;
}

interface IWidget {
  editor?: IEditorBrief | null;
  /** 协作者：人类授权代写的 AI 模型（v3 的 co_editor） */
  coEditor?: IEditorBrief | null;
  updatedAt?: string;
}

/**
 * 「编辑者（+ 协作者）· 编辑于 N 天前」一行。
 * 两个头像用 antd 的头像组叠放，悬停显示名字。
 */
const EditorsLine = ({ editor, coEditor, updatedAt }: IWidget) => {
  const intl = useIntl();
  const people = [editor, coEditor].filter((p): p is IEditorBrief => !!p);
  if (people.length === 0 && !updatedAt) {
    return null;
  }

  return (
    <Space size="small" wrap>
      {people.length > 0 && (
        <Avatar.Group size="small">
          {people.map((p, i) => (
            <Tooltip
              key={i}
              title={p.userName ? `${p.nickName}@${p.userName}` : p.nickName}
            >
              <Avatar
                src={p.avatar || undefined}
                style={
                  p.avatar
                    ? undefined
                    : { backgroundColor: getAvatarColor(p.nickName) }
                }
              >
                {p.nickName?.slice(0, 2)}
              </Avatar>
            </Tooltip>
          ))}
        </Avatar.Group>
      )}
      {people.length > 0 && (
        <Text>{people.map((p) => p.nickName).join(" & ")}</Text>
      )}
      {updatedAt && (
        <TimeShow
          type="secondary"
          showIcon={false}
          title={intl.formatMessage({ id: "labels.edited-at" })}
          updatedAt={updatedAt}
        />
      )}
    </Space>
  );
};

export default EditorsLine;

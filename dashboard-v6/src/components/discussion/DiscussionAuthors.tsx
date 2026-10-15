import { Avatar, Tooltip } from "antd";

import User from "../auth/User";
import { getAvatarColor } from "../auth/utils";
import type { IUser } from "../../api/Auth";

interface IActor {
  user: IUser;
  role: string;
}

/** Avatar.Group 的子元素必须直接是 Avatar（Tooltip 不包额外 DOM），重叠样式才生效 */
const ActorAvatar = ({ user, role }: IActor) => (
  <Tooltip title={`${user.nickName}（${role}）`}>
    <Avatar
      src={user.avatar}
      style={
        user.avatar
          ? undefined
          : { backgroundColor: getAvatarColor(user.nickName) }
      }
    >
      {user.nickName?.slice(0, 2)}
    </Avatar>
  </Tooltip>
);

interface IWidget {
  user: IUser;
  coEditor?: IUser;
}

/**
 * 讨论的作者头像。AI 凭人类授权代写时（有 co_editor），用 Avatar.Group 并排显示
 * 授权人与模型两个头像；否则只显示作者一个。
 */
const DiscussionAuthors = ({ user, coEditor }: IWidget) =>
  coEditor ? (
    <Avatar.Group size="small">
      <ActorAvatar user={user} role="授权人" />
      <ActorAvatar user={coEditor} role="AI 协作者" />
    </Avatar.Group>
  ) : (
    <User {...user} showName={false} />
  );

export default DiscussionAuthors;

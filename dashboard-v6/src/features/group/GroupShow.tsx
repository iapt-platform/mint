import { useIntl } from "react-intl";
import { useNavigate } from "react-router";
import { Button, Card, Popconfirm, Space } from "antd";
import { Col, Row } from "antd";
import { SettingOutlined } from "@ant-design/icons";

import GroupFile from "../../components/group/GroupFile";
import GroupMember from "../../components/group/GroupMember";
import { removeGroupMember } from "../../api/group-member";
import { useAppDispatch, useAppSelector } from "../../hooks";
import { studioList } from "../../reducers/current-user";
import { refreshMyGroups } from "../teamspace/refreshMyGroups";

interface IWidget {
  teamId?: string;
  onSetting?: () => void;
}
const GroupShow = ({ teamId, onSetting }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const studios = useAppSelector(studioList);

  // 我在这个团队里的成员记录；owner 不能退出
  const mine = studios?.find((item) => item.id === teamId);
  const canLeave = !!mine?.memberId && mine.role !== "owner";

  return (
    <Card
      extra={
        <Space>
          {canLeave && (
            <Popconfirm
              title={intl.formatMessage({ id: "forms.message.group.leave" })}
              onConfirm={async () => {
                if (!teamId || !mine?.memberId) {
                  return;
                }
                await removeGroupMember(teamId, mine.memberId);
                // 退出的若正是当前团队空间，refreshMyGroups 会顺带切回个人空间
                await refreshMyGroups(dispatch);
                navigate("/workspace/team");
              }}
              okText={intl.formatMessage({ id: "buttons.ok" })}
              cancelText={intl.formatMessage({ id: "buttons.cancel" })}
            >
              <Button type="link" danger>
                {intl.formatMessage({ id: "buttons.group.exit" })}
              </Button>
            </Popconfirm>
          )}
          <Button type="link" icon={<SettingOutlined />} onClick={onSetting} />
        </Space>
      }
    >
      <Row>
        <Col flex="auto" style={{ paddingRight: 10 }}>
          <GroupFile groupId={teamId} />
        </Col>
        <Col flex="380px">
          <GroupMember groupId={teamId} />
        </Col>
      </Row>
    </Card>
  );
};

export default GroupShow;

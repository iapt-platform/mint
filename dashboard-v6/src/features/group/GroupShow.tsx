import { useIntl } from "react-intl";
import { Button, Card, Space } from "antd";
import { Col, Row } from "antd";
import { SettingOutlined } from "@ant-design/icons";

import GroupFile from "../../components/group/GroupFile";
import GroupMember from "../../components/group/GroupMember";

interface IWidget {
  teamId?: string;
  onSetting?: () => void;
}
const GroupShow = ({ teamId, onSetting }: IWidget) => {
  const intl = useIntl();

  return (
    <Card
      extra={
        <Space>
          <Button type="link" danger>
            {intl.formatMessage({ id: "buttons.group.exit" })}
          </Button>
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
